#!/bin/bash -xe

if [ ! -d vendor/drutiny/website ]; then
  echo "Cannot publish: Website is not installed. Run `composer install`."
  exit 1;
fi

if [ ! -d vendor/drutiny/website/.git ]; then
  echo "Cannot publish: Installed website is not a git respository."
  exit 1;
fi

if [ ! -f build/REVISION ]; then
  echo "No REVISION present in build."
  exit 1;
fi

REF=`cat build/REVISION`

pushd vendor/drutiny/website

git config user.name "Drutiny Content Deployment"
git config user.email "content@drutiny.ci"

UPDATES=`git status --porcelain api/`

if [ "$UPDATES" != "" ]; then
	git add api/
	git commit -m "Publishing revision $REF from drutiny/content."
	git push

  curl -XPOST -H'content-type:application/json' "https://packagist.org/api/update-package?username=fiasco&apiToken=$PACKAGIST_API_TOKEN" -d'{"repository":{"url":"https://github.com/drutiny/content"}}'
fi
