pipeline {
  agent any
  stages {
    stage('Checkout') { steps { checkout scm } }
    stage('Install') { steps { sh 'composer install --no-interaction --prefer-dist' } }
    stage('Security Audit') { steps { sh 'composer audit --no-interaction' } }
    stage('Prepare') { steps { sh 'cp .env.example .env || true'; sh 'php artisan key:generate --force'; sh 'touch database/database.sqlite'; sh 'DB_CONNECTION=sqlite DB_DATABASE=database/database.sqlite php artisan migrate --force' } }
    stage('Tests and Coverage') { steps { sh 'composer test:coverage' } }
  }
  post { always { archiveArtifacts artifacts: 'storage/logs/*.log,coverage/**', allowEmptyArchive: true } }
}
