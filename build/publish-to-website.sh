#!/bin/bash -xe

if [ ! -d vendor/drutiny/website ]; then
  echo "Cannot publish: Website is not installed. Run `composer install`."
  exit 1;
fi

if [ ! -d vendor/drutiny/website/.git ]; then
  echo "Cannot publish: Installed website is not a git respository."
  exit 1;
fi

if [ ! -f build/id_rsa ]; then
  echo "Cannot publish: ssh private key is not present. Please run script via Travis CI."
  exit 1;
fi

# Install SSH key.
IDENTITY_FILE="`pwd`/build/id_rsa"
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
