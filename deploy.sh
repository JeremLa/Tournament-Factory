#!/bin/bash
if [ "$TRAVIS_BRANCH" == "master" ]; then
    echo "start deployment"
    gitLastCommit=$(git show --summary --grep="Merge pull request")
    echo "git last commit :"
    echo "$gitLastCommit"
    if [[ -z "$gitLastCommit" ]]
    then
        lastCommit=$(git log --format="%H" -n 1)
    else
        echo "We got a Merge Request!"
        #take the last commit and take break every word into an array
        arr=($gitLastCommit)
        #the 5th element in the array is the commit ID we need. If git log changes, this breaks. :(
        lastCommit=${arr[4]}
    fi
    echo $lastCommit

    filesChanged=$(git diff-tree --no-commit-id --name-only -r $lastCommit)
    echo "files changed:"
    echo "$filesChanged"
    if [ ${#filesChanged[@]} -eq 0 ]; then
        echo "No files to update"
    else
        hasComposer="false"
        for f in $filesChanged
        do
            if [ "$f" == "composer.json" ]
            then
                hasComposer="true"
            fi
        done     
        if [ "$hasComposer" == "true" ]
        then
            echo "ssh request with composer install."
            sshpass -p $SSH_PASS ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST 'cd /var/www/Tournament-Factory; git pull origin master; sudo -n setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log; composer install --no-dev --optimize-autoloader; php bin/console doctrine:migrations:migrate; sudo -n setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log; php bin/console cache:clear ;sudo -n setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log'

        else
            echo "ssh request without composer install."
            sshpass -p $SSH_PASS ssh -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST 'cd /var/www/Tournament-Factory; git pull origin master; sudo -n setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log; php bin/console doctrine:migrations:migrate; php bin/console cache:clear ;sudo -n setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/log'
        fi
    fi
fi
