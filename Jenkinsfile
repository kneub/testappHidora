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
        sh "docker login -u cjbg -p KwM1OQbomRtwMuqG7zvu"
        sh "docker login node79989-cjb-private-registry.hidora.com:5000 -u root -p 34Njsk4MLu"
    }

    stage('build and push') {
        app = docker.build("node79989-cjb-private-registry.hidora.com:5000/testapp")
        app.push("dev")
    }

    stage('deploy') {
        sh "kubectl apply -f docker/"
	}
}
