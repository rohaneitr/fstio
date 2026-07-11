# Verification Snapshot

## composer dump-autoload
FAILED
Error: `composer : The term 'composer' is not recognized as the name of a cmdlet, function, script file, or operable program.`

## vendor/bin/pint --test
RUNNING / FAILED
Likely failed or stuck due to PHP not being installed/recognized in the current PATH.

## vendor/bin/phpstan analyse --level=9
FAILED
No output, likely failed due to missing PHP binary.

## php artisan test
FAILED
Error: `php : The term 'php' is not recognized as the name of a cmdlet, function, script file, or operable program.`
