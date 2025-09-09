pipeline {
    agent any
    stages {
        stage('helloworld') {
            steps {
                echo 'Hello from Avinash for Jenkins pipeline'
            }
        }
    }
}

---

pipeline {
    agent any
    stages {
        stage('checkout') {
            steps {
                echo 'getting the code from github'
            }
        }
        stage('Test') {
            steps {
                echo 'application is testing now'
            }
        }
        stage('Deploy') {
            steps {
                echo 'deploying our application'
            }
        }
    }
}

---

pipeline {
    agent any
    stages {
        stage('shellstage') {
            steps {
                sh 'df -Th'
            }
        }
    }
}

---

pipeline {
    agent any
    stages {
        stage('stage-1') {
            steps {
                echo 'stage-1 is executed'
            }
        }
        stage('stage-2') {
            steps {
                git branch: 'main', url: 'https://github.com/avizway1/awar04-jenkins.git'
            }
        }
    }
}

---

#pipeline as code

pipeline {
    agent any
    stages {
        stage ('shell') {
            steps {
                sh 'id'
                sh 'uname'
                sh 'df -Th'
                sh 'date'
            }
        }
    }
}

---

pipeline {
    agent any
    stages {
        stage ('shell') {
            steps {
                sh'''
                'id'
                'uname'
                'df -Th'
                date
                '''
            }
        }
    }
}

---

pipeline {
    agent any
    tools {
        maven 'maven'   // name you configured in Jenkins tools
    }
    stages {
        stage('checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/avizway1/awar04-jenkins.git'
            }
        }
        stage('build') {
            steps {
                sh 'mvn clean package'
            }
        }
        stage('test') {
            steps {
                sh 'mvn test'
            }
        }
    }
}


---

pipeline {
    agent any
    tools {
        maven 'maven'
    }
    environment {
        TOMCAT_URL = "http://13.232.220.174:8080/manager/text"
        WAR_PATH   = "Springdemo-0.0.1-SNAPSHOT"  // context path
    }
    stages {
        stage('checkout') {
            steps {
                git branch: 'main', url: 'https://github.com/avizway1/awar04-jenkins.git'
            }
        }
        stage('build') {
            steps {
                sh 'mvn clean package'
            }
        }
        stage('test') {
            steps {
                sh 'mvn test'
            }
        }
        stage('deploy') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'tomcat', usernameVariable: 'TOMCAT_USER', passwordVariable: 'TOMCAT_PASS')]) {
                    echo "Undeploying old app from Tomcat..."
                    sh """
                        curl -s -o /dev/null -w "%{http_code}" -u $TOMCAT_USER:$TOMCAT_PASS \
                        "${TOMCAT_URL}/undeploy?path=/${WAR_PATH}" || true
                    """

                    echo "Deploying new WAR to Tomcat..."
                    sh """
                        curl -s -o /dev/null -w "%{http_code}" -u $TOMCAT_USER:$TOMCAT_PASS \
                        -T target/${WAR_PATH}.war "${TOMCAT_URL}/deploy?path=/${WAR_PATH}&update=true"
                    """
                }
            }
        }
    }
}

---