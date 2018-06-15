#!/bin/bash
echo "start deployment"
current_path=$(pwd)
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
    hasComposer="true"
	if [ "$hasComposer" == "true" ]
	then
# 	    lftp -f "
# 	    set dns:order 'inet'
# 	    open ftp://$FPT_HOST
# 	    user $FTP_USER $FTP_PASS
# 	    mirror --continue --reverse --delete $current_path '/public_html'
# 	    bye
# 	    "
	    
	    lftp -c "set dns:order 'inet'" -e "mirror -R $current_path /public_html" -u $FTP_USER,$FTP_PASS ftp://$FTP_HOST
#	    for file in "$current_path"/*
#        do
#            # file= "$file | cut -d'/' -f7-"
##             curl --ftp-create-dirs -T $file -u $FTP_USER:$FTP_PASS ftp://$FTP_HOST/public_html/$file
#
#        done
	else
        for f in $filesChanged
        do
            if [ "$f" != ".travis.yml" ] && [ "$f" != "deploy.sh" ] && [ "$f" != "test.js" ] && [ "$f" != "composer.json" ]
            then
                echo "Uploading $f"
                curl --ftp-create-dirs -T $f -u $FTP_USER:$FTP_PASS ftp://$FTP_HOST/public_html/$f
            fi
        done
    fi
fi
