#!/bin/bash

if [ ! -d vendor/drutiny/website ]; then
  echo "Cannot publish: Website is not installed. Run `composer install`."
  exit 1;
fi

if [ ! -d vendor/drutiny/website/.git ]; then
  echo "Cannot publish: Installed website is not a git respository."
  exit 1;
fi

# Install SSH key.
openssl aes-256-cbc -K $encrypted_9472126ed793_key -iv $encrypted_9472126ed793_iv -in docs/ghp-id_rsa.enc -out ghp-id_rsa -d
chmod 0400 ghp-id_rsa
IDENTITY_FILE="`pwd`/ghp-id_rsa"
export GIT_SSH_COMMAND="ssh -i $IDENTITY_FILE"

REF=`git log --pretty="%H" -1`

pushd vendor/drutiny/website

git config user.name "Travis CI"
git config user.email "drutiny@travis.ci"

UPDATES=`git status --porcelain api/`

if [ "$UPDATES" != "" ]; then
	git add api/
	git commit -m "Publishing revision $REF from drutiny/content."
	git push
fi
