node {

	properties([
    	buildDiscarder(logRotator(numToKeepStr:'5'))
  	])

    /* Requires the Docker Pipeline plugin to be installed */
    def app

    stage('Clone repository') {
      /* Let's make sure we have the repository cloned to our workspace */
      checkout scm
	}

	stage('SED Deployment variable') {
        script {
            env.DEPLOY_COMMIT_HASH = sh(returnStdout: true, script: "git rev-parse HEAD | cut -c1-7").trim()
            env.DEPLOY_BUILD_DATE = sh(returnStdout: true, script: "date -u +'%Y-%m-%dT%H.%M.%SZ'").trim()
        }

        dir ('k8s/deploy') {
            sh "sed -i \"s|DATE_DEPLOYMENT|${env.DEPLOY_BUILD_DATE}|g\" 100-deployment.yaml"
            sh "sed -i \"s|DEPLOY_COMMIT_HASH|${env.DEPLOY_COMMIT_HASH}|g\" 100-deployment.yaml"
            sh "sed -i \"s|BRANCH_NAME|${env.BRANCH_NAME}|g\" 100-deployment.yaml"
        }
    }

    stage('Login registry') {
        sh "docker login -u cjbg -p KwM1OQbomRtwMuqG7zvu"
        sh "docker login node79989-cjb-private-registry.hidora.com:5000 -u root -p 34Njsk4MLu"
    }

    stage('Build and Push Init Containers') {
        dir ('k8s/initContainers') {
            init = docker.build("node79989-cjb-private-registry.hidora.com:5000/bota-ani-confd:${env.DEPLOY_COMMIT_HASH}");
            init.push();
        }
    }

    stage('build and push') {
        app = docker.build("node79989-cjb-private-registry.hidora.com:5000/testapp:${env.DEPLOY_COMMIT_HASH}")
        app.push()
    }

    stage('deploy') {
        sh "kubectl apply -f k8s/deploy"
	}
}
