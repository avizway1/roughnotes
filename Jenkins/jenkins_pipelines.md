# Jenkins Pipeline Examples with Explanations

---

## 1. Hello World Pipeline
This is the most basic pipeline. It just prints a message in the Jenkins console log.

```groovy
pipeline {
    agent any
    stages {
        stage('Hello') {
            steps {
                echo 'Hello, Jenkins Pipeline!'
            }
        }
    }
}
```

📌 **Description:**  
- Defines a simple pipeline with one stage (`Hello`).  
- `echo` is used to print a message.  
- Good for testing that your Jenkins pipeline setup works.

---

## 2. Multiple Stages Example
This example introduces multiple stages (Build, Test, Deploy).

```groovy
pipeline {
    agent any
    stages {
        stage('Build') {
            steps {
                echo 'Building the application...'
            }
        }
        stage('Test') {
            steps {
                echo 'Running tests...'
            }
        }
        stage('Deploy') {
            steps {
                echo 'Deploying application...'
            }
        }
    }
}
```

📌 **Description:**  
- Shows a basic CI/CD pipeline flow.  
- Splits work into **three stages**: Build → Test → Deploy.  
- Each stage runs sequentially.

---

## 3. Simple Command Example
Running a simple shell command (`uname`) inside a pipeline.

```groovy
pipeline {
    agent any
    stages {
        stage('Hello') {
            steps {
                sh 'uname'
            }
        }
    }
}
```

📌 **Description:**  
- Demonstrates using `sh` step to run shell commands.  
- `uname` prints OS details.  
- Useful to confirm Jenkins agent environment.

---

## 4. Syntax Generator Example
A slightly more advanced example with multiple stages, one running a command and another checking out code.

```groovy
pipeline {
    agent any
    stages {
        stage('stage-1') {
            steps {
                sh 'hostname'
            }
        }
        stage('stage-2') {
            steps {
                git branch: 'main', url: 'https://github.com/avizway1/awar04-jenkins.git'
            }
        }
    }
}
```

📌 **Description:**  
- `stage-1`: Runs `hostname` to display machine name.  
- `stage-2`: Uses Jenkins Git plugin to checkout code from GitHub.  
- Great example of combining system commands with SCM checkout.

---

### Quick Notes (Easy to Remember)
- `pipeline {}` → Starts the pipeline definition.  
- `agent any` → Run on any available Jenkins agent.  
- `stages {}` → Defines different steps of the pipeline.  
- `echo` → Prints text to the console log.  

---

## 5. Pipeline as Code
Demonstrates multiple steps inside a stage (command, echo, Git clone).

```groovy
pipeline {
    agent any
    stages {
        stage('Hello') {
            steps {
                sh 'uname'
                echo "second step"
                git branch: 'main', url: 'https://github.com/avizway1/awar04-jenkins.git'
            }
        }
    }
}
```

📌 **Description:**  
- Runs three tasks in the same stage:  
  1. Print OS info.  
  2. Print a message.  
  3. Clone GitHub repo.  
- Demonstrates how a stage can have multiple `steps`.

---

## 6. Multiple Shell Commands
Run multiple shell commands in a single step using `'''`.

```groovy
pipeline {
    agent any
    stages {
        stage('Hello') {
            steps {
                sh '''
                ls
                lsblk
                df -Th
                hostname
                '''
            }
        }
    }
}
```

📌 **Description:**  
- Executes several Linux commands in one `sh` block.  
- Useful when you need to run grouped commands together.  
- Commands print filesystem, disk, and hostname info.

---

## 7. Scripted Pipeline (Single Stage)
Scripted pipelines use Groovy style with `node {}` blocks.

```groovy
node {
    stage('Build') {
        sh 'echo "hello from avinash"'
    }
}
```

📌 **Description:**  
- Scripted pipeline style.  
- Runs inside a Jenkins `node`.  
- Executes a single build stage.

---

## 8. Scripted Pipeline (Multiple Stages)
A more complete scripted pipeline with three stages.

```groovy
node {
    stage('Build') {
        echo 'Building...'
    }
    stage('Test') {
        echo 'Testing...'
    }
    stage('Deploy') {
        echo 'Deploying...'
    }
}
```

📌 **Description:**  
- Scripted format of a Build → Test → Deploy flow.  
- Similar logic to declarative pipelines but written differently.  
- Preferred if you need complex Groovy scripting.

---

## 9. Pipeline with Shell Commands (Real Example)
Shows a typical CI/CD workflow with Git + Maven build + tests.

```groovy
pipeline {
    agent any
    stages {
        stage('Checkout') {
            steps {
                sh 'git clone https://github.com/avizway1/awar04-jenkins.git'
            }
        }
        stage('Build') {
            steps {
                sh 'echo "Compiling code..."'
                sh 'mvn clean package'
            }
        }
        stage('Test') {
            steps {
                sh 'mvn test'
            }
        }
    }
}
```

📌 **Description:**  
- Stage 1: Clones source code from GitHub.  
- Stage 2: Builds code using Maven.  
- Stage 3: Runs Maven tests.  
- Mimics real-world Java CI/CD.

---

## 10. Pipeline with Post Actions
Adds conditional actions after pipeline finishes.

```groovy
pipeline {
    agent any
    stages {
        stage('Build') {
            steps {
                sh 'echo "Building project..."'
                sh 'sleep 5'  // simulate build
            }
        }
        stage('Test') {
            steps {
                sh 'echo "Running tests..."'
                sh 'exit 1'   // force failure
            }
        }
    }
    post {
        success {
            echo '✅ Build succeeded!'
        }
        failure {
            echo '❌ Build failed!'
        }
        always {
            echo '📢 Pipeline finished (success or fail).'
        }
    }
}
```

📌 **Description:**  
- Demonstrates `post` section in pipelines.  
- `success`: Runs if pipeline succeeds.  
- `failure`: Runs if pipeline fails.  
- `always`: Runs regardless of outcome.  
- Useful for notifications, cleanup, or reporting.


## 11. Pipeline with Post Actions

Adds approval process.

```groovy
pipeline {
    agent any
    stages {
        stage('Confirmation') {
            steps {
                script {
                    input message: '🚦 Do you want to continue?', ok: 'Yes'
                }
            }
        }
        stage('Next Step') {
            steps {
                echo "Pipeline continued after approval ✅"
            }
        }
    }
}

```
Adds approval process.

