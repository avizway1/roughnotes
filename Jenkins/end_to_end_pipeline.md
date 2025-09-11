# End-to-End CI/CD: Maven → SonarQube → Nexus → Tomcat (Jenkins)

This guide gives you a **ready-to-run Jenkins Declarative Pipeline** and the **exact prerequisites** to build a Spring Boot app (WAR), analyze it with SonarQube, publish to Nexus, and deploy to Tomcat.

> The pipeline below matches your latest working setup: **no Quality Gate stage**, **hardcoded Sonar token**, Nexus credentials via Jenkins **`nexus-creds`**, and Tomcat credentials via Jenkins **`tomcat`**.


---

## 1) Prerequisites

### Jenkins plugins
- **Pipeline** (Workflow: Aggregator)
- **Git** plugin
- **SonarQube Scanner for Jenkins**
- (Optional) **Credentials Binding** (comes with Jenkins core in recent versions)

### Jenkins tools (Manage Jenkins → Global Tool Configuration)
- **Maven** tool named: `maven`

### SonarQube (running at `http://localhost:9000`)
- Server is up and reachable from Jenkins.
- In Jenkins → **Manage Jenkins → System → SonarQube servers**: add a server named **`MySonarQube`** with URL `http://localhost:9000`.
- You are using a **hardcoded token** in the pipeline for this test (see env `SONAR_TOKEN`).

### Nexus Repository (running at `http://localhost:8081`)
- Create (or use existing) repositories:
  - `maven-releases` (hosted)
  - `maven-snapshots` (hosted)
- Your `pom.xml` includes:

  ```xml
  <distributionManagement>
    <repository>
      <id>nexus-releases</id>
      <url>http://localhost:8081/repository/maven-releases/</url>
    </repository>
    <snapshotRepository>
      <id>nexus-snapshots</id>
      <url>http://localhost:8081/repository/maven-snapshots/</url>
    </snapshotRepository>
  </distributionManagement>
  ```

### Jenkins credentials (Manage Jenkins → Credentials)
- **ID:** `nexus-creds` → **Username/Password** for Nexus (e.g., `admin` / `avinash`).
- **ID:** `tomcat` → **Username/Password** with Tomcat `manager-script` role (user must exist in Tomcat).

### Tomcat (target server)
- URL: `http://172.31.32.201:8080`
- Restart Tomcat after editing users.
- Security Group/Firewall allows **TCP 8080** from Jenkins host.

### Network/Ports
- Jenkins → SonarQube: **9000**
- Jenkins → Nexus: **8081**
- Jenkins → Tomcat: **8080**

---

## 2) Jenkinsfile (latest working, without Quality Gate)

> Uses: hardcoded Sonar token, Nexus creds via `nexus-creds`, Tomcat creds via `tomcat`.

```groovy
pipeline {
  agent any

  tools {
    maven 'maven'
  }

  environment {
    // --- SonarQube ---
    SONAR_SERVER_NAME = 'MySonarQube'
    SONAR_PROJECT_KEY = 'github_avizway1_awar04_jenkins'
    SONAR_PROJECT_NAME = 'Springdemo'
    SONAR_HOST_URL    = 'http://localhost:9000'
    SONAR_TOKEN       = 'squ_36a1c47e013bd088f8979ef29645bb34bce5c8b7'  // ⚠️ Hardcoded for testing

    // --- Nexus ---
    NEXUS_URL         = 'http://localhost:8081'
    RELEASE_REPO_ID   = 'nexus-releases'
    SNAPSHOT_REPO_ID  = 'nexus-snapshots'
    MVN_SETTINGS_FILE = '.mvn-settings.xml'

    // --- Tomcat ---
    TOMCAT_BASE_URL   = 'http://172.31.32.201:8080'
    TOMCAT_APP_PATH   = '/springdemo'
  }

  stages {
    stage('Checkout') {
      steps {
        git url: 'https://github.com/avizway1/awar04-jenkins.git', branch: 'main'
      }
    }

    stage('Build + SonarQube Analysis') {
      steps {
        withSonarQubeEnv("${SONAR_SERVER_NAME}") {
          sh '''
            mvn -B -DskipTests=true clean verify sonar:sonar               -Dsonar.projectKey=${SONAR_PROJECT_KEY}               -Dsonar.projectName=${SONAR_PROJECT_NAME}               -Dsonar.host.url=${SONAR_HOST_URL}               -Dsonar.login=${SONAR_TOKEN}
          '''
        }
      }
    }

    stage('Prepare Maven Settings for Nexus') {
      steps {
        withCredentials([usernamePassword(credentialsId: 'nexus-creds',
                                          usernameVariable: 'NEXUS_USER',
                                          passwordVariable: 'NEXUS_PASS')]) {
          sh '''
cat > ${MVN_SETTINGS_FILE} <<EOF
<settings xmlns="http://maven.apache.org/SETTINGS/1.0.0"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://maven.apache.org/SETTINGS/1.0.0 https://maven.apache.org/xsd/settings-1.0.0.xsd">
  <servers>
    <server>
      <id>${RELEASE_REPO_ID}</id>
      <username>${NEXUS_USER}</username>
      <password>${NEXUS_PASS}</password>
    </server>
    <server>
      <id>${SNAPSHOT_REPO_ID}</id>
      <username>${NEXUS_USER}</username>
      <password>${NEXUS_PASS}</password>
    </server>
  </servers>
</settings>
EOF
          '''
        }
      }
    }

    stage('Deploy to Nexus') {
      steps {
        sh '''
          mvn -B -DskipTests=true deploy -s ${MVN_SETTINGS_FILE}
        '''
      }
    }

    stage('Deploy to Tomcat') {
      steps {
        withCredentials([usernamePassword(credentialsId: 'tomcat',
                                          usernameVariable: 'TC_USER',
                                          passwordVariable: 'TC_PASS')]) {
          sh '''
            WAR_FILE=$(ls target/*.war | head -n 1)
            [ -z "$WAR_FILE" ] && { echo "No WAR found in target/"; exit 1; }

            set -e
            curl -sS -u "$TC_USER:$TC_PASS" --fail               --upload-file "$WAR_FILE"               "${TOMCAT_BASE_URL}/manager/text/deploy?path=${TOMCAT_APP_PATH}&update=true" || {

              echo "Update failed; attempting fresh deploy…"
              curl -sS -u "$TC_USER:$TC_PASS" --fail                 "${TOMCAT_BASE_URL}/manager/text/undeploy?path=${TOMCAT_APP_PATH}" || true

              curl -sS -u "$TC_USER:$TC_PASS" --fail                 --upload-file "$WAR_FILE"                 "${TOMCAT_BASE_URL}/manager/text/deploy?path=${TOMCAT_APP_PATH}"
            }

            echo "✅ Deployed ${WAR_FILE} to ${TOMCAT_BASE_URL}${TOMCAT_APP_PATH}"
          '''
        }
      }
    }
  }

  post {
    always {
      archiveArtifacts artifacts: 'target/**/*.war', allowEmptyArchive: true
      sh 'rm -f ${MVN_SETTINGS_FILE} || true'
    }
  }
}
```

---

## 3) Run it
1. Create a **Pipeline** job in Jenkins.  
2. Paste the Jenkinsfile above.  
3. Ensure credentials `nexus-creds` and `tomcat` exist in Jenkins.  
4. Build the job.  
5. Watch stages: **Checkout → Build + Sonar → Deploy to Nexus → Deploy to Tomcat**.

---

## 4) Notes & Common Issues
- **401/Not authorized on Sonar**: Ensure `SONAR_TOKEN` is valid and SonarQube is reachable.  
- **Nexus deploy fails**: Confirm `distributionManagement` in `pom.xml` and that `nexus-creds` matches Nexus users.  
- **Tomcat deploy fails**: Ensure the `manager-script` role and that Jenkins can reach port **8080** on the Tomcat server.  
- **Multiple repos**: Change `SONAR_PROJECT_KEY` per repo to avoid Sonar projects colliding.

---

**You’re set!** This is a solid, minimal end-to-end CI/CD pipeline for a Spring Boot WAR to SonarQube, Nexus, and Tomcat.
