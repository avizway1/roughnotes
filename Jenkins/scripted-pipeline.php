node {
    stage('build') {
        sh 'echo "hello from AVinash"'
    }
}

---

node {
    stage('hello') {
        sh 'echo "hello from AVinash"'
    }
    stage('test') {
        sh 'echo "we are testing here"'
    }
    stage('build') {
        sh 'echo "we are building here"'
    }
}

---

