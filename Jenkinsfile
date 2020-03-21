node {

	properties([
    	buildDiscarder(logRotator(numToKeepStr:'5'))
  	])

    /* Requires the Docker Pipeline plugin to be installed */
    def app
    def STACK_NAME = "MSM_TEST"

    stage('Clone repository') {
        echo 'test console'
        echo "stack_name ...: ${STACK_NAME}"

        /* Let's make sure we have the repository cloned to our workspace */
        // checkout scm
		}

	stage('Build  and push image') {
	    input "Does the staging rc environment look ok?"
        /* This builds the actual image; synonymous to
         * docker build on the command line */
        app = docker.build("${STACK_NAME}:dev");
        app.push()
	}

	// Production, only on branch Master
	 switch (env.BRANCH_NAME) {
	    case "dev":
            stage('Deploy msm-export-excel On Docker DEV environment') {
                     sh """\
                         STACK_NAME=${STACK_NAME} \
                         BRANCH=${env.BRANCH_NAME} \
                         DOCKER_HOST=tcp://172.17.0.1:3272 \
                         docker stack deploy ${STACK_NAME} --compose-file docker-compose.yml --resolve-image always --prune"""
            }
            break
   	}

}
