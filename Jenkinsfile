node {

	properties([
    	buildDiscarder(logRotator(numToKeepStr:'5'))
  	])

    /* Requires the Docker Pipeline plugin to be installed */
    def app
    def STACK_NAME = "MSM_TEST"


    stage('test') {
        echo "NODE_NAME = ${env.NODE_NAME}"
        echo 'test console'
        echo "stack_name ...aa: ${STACK_NAME}"
        sh 'docker -v'
        /* Let's make sure we have the repository cloned to our workspace */
        // checkout scm
	}

}
