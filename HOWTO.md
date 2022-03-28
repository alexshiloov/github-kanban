# How to run application locally

0. Install `docker-compose ` https://docs.docker.com/compose/install/
1. Change `GH_CLIENT_ID`, `GH_CLIENT_SECRET`, `GH_ACCOUNT`, `GH_REPOSITORIES`
    * You can get `GH_CLIENT_ID` and `GH_CLIENT_SECRET` when you register new OAuth application https://github.com/settings/applications/new  
    * `GH_ACCOUNT` is you username in GitHub, `GH_REPOSITORIES` - list of your repositories
2. Run `docker-compose up -d --build`
3. Kanban is available on `http://localhost/`

# How to run tests

Run `docker-compose -f docker-compose.test.yml up --remove-orphans --build`