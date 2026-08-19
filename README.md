# AldafluxStandardUserCommandBundle

## Installation

### Step 1 : Download the bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```sh
composer require aldaflux/standard-user-command-bundle
```
    
This command is used if composer is installed in your system.
    
## Usage

### List users

Lists all the users registered in the application.

```sh
./bin/console suc:user:list
```

By default, the command only displays the 50 most recent users. You can set the number of results to display with the `--max-results` option:

```sh
./bin/console suc:user:list --max-results=2000
```

### Add user

Creates new users and saves them in the database.

```sh
./bin/console suc:user:add 
```

You can provide the arguments directly:
```sh
./bin/console suc:user:add username password email full-name
```

By default the command creates regular users. To create administrator users, add the `--admin` option:
```sh
./bin/console suc:user:add username password email --admin
```

If you omit any of the required arguments, the command will interactively ask you to provide the missing values.

### Change password

Change the password of an existing user.

```sh
./bin/console suc:user:change-password
```

You can specify the identifier (username or email) directly:
```sh
./bin/console suc:user:change-password username
./bin/console suc:user:change-password user@example.com
```

You can alternatively specify the new password as a second argument:
```sh
./bin/console suc:user:change-password username newpassword
```
