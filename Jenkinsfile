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
        sh " cjb-registry.hidora.com -u hidora.cjb@ville-ge.ch -p Xd4t78TIR5"
    }

    stage('test') {
        echo 'test console'
        echo "stack_name ...aa: ${STACK_NAME}"
        sh "ls  docker"
	}

	/* stage('Build') {
        docker.image('node:9.3.0').inside('-P') {
            sh 'node -v'
        }
    } */

}
