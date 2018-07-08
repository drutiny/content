# Drutiny Content
![Build Status](https://travis-ci.org/drutiny/content.svg?branch=master)

This respository provides policies and profiles for Drutiny in YAML format compatible with the Drutiny
`localFs` [PolicySource](https://github.com/drutiny/drutiny/blob/2.2.x/src/PolicySource/LocalFs.php) and 
ProfileSource adapters.

The contents of this repository are compiled into the API endpoints found at https://drutiny.github.io/api/v2/
which are consumed by the `DrutinyGithubIo` [PolicySource](https://github.com/drutiny/drutiny/blob/2.2.x/src/PolicySource/DrutinyGitHubIO.php)
and ProfileSource adapters.

This respository is available as a [composer package](https://packagist.org/packages/drutiny/content), generally used for local development. It is a `require-dev`
dependency for `drutiny/drutiny`.
