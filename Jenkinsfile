node {

	properties([
    	buildDiscarder(logRotator(numToKeepStr:'5'))
  	])

    /* Requires the Docker Pipeline plugin to be installed */
    def app
    def STACK_NAME = "MSM_TEST"

    stage('Clone repository') {
      /* Let's make sure we have the repository cloned to our workspace */
      checkout scm
	}

    stage('Login registry') {
        sh "docker login node79989-cjb-private-registry.hidora.com:5000 -u root -p 34Njsk4MLu"
    }

    stage('build and push') {
            app = docker.build("node79989-cjb-private-registry.hidora.com:5000/testapp")
            app.push("latest")
        }

    stage('deploy') {
        echo 'test console'
        echo "stack_name ...aa: ${STACK_NAME}"
        sh "ls  docker"
        sh "kubectl apply -f docker/"
	}

	/* stage('Build') {
        docker.image('node:9.3.0').inside('-P') {
            sh 'node -v'
        }
    } */

}
