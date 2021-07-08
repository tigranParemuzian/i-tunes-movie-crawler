# iTunes Movie trailers! Basic version

## Setup

This version of the test and don't expect perfect approaches from this. Thanks for understanding.

If you've just downloaded the code, congratulations!!

To get it working, follow these steps:

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```

You may alternatively need to run `php composer.phar install`, depending
on how you installed Composer.

**Configure the .env (or .env.local) File**

Open the `.env` file and make any adjustments you need - specifically
`DATABASE_URL`, `MAILER_DSN`, `ADMIN_EMAIL` `MESSENGER_TRANSPORT_DSN`. Or, if you want, you can create a `.env.local` file
and *override* any configuration you need there (instead of changing
`.env` directly).

**Setup the Database**

Again, make sure `.env` is setup for your computer. Then, create
the database & tables!

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate | php bin/console doctrine:s:u --force
```

If you get an error that the database exists, that should
be ok. But if you have problems, completely drop the
database (`doctrine:database:drop --force`) and try again.

**For the default Movie load

```
php bin/console app:fetch:trailers
```

**Start the built-in web server**

You can use Nginx or Apache, but Symfony's local web server
works even better or use `docker`.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve
```

(If this is your first time using this command, you may see an
error that you need to run `symfony server:ca:install` first).

Now check out the site at `https://localhost:8000/api/doc`

You need to configure symfony [messenger](https://symfony.com/doc/current/messenger.html)
```angular2html
php bin/console messenger:consume async -vv
```

For the production try to use [Supervisor](https://symfony.com/doc/current/messenger.html)
```angular2html
;/etc/supervisor/conf.d/messenger-worker.conf
[program:messenger-consume]
command=php /path/to/your/app/bin/console messenger:consume async --time-limit=3600
user=ubuntu
numprocs=2
startsecs=0
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
```
Have fun!

## Thanks!