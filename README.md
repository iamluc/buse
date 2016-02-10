BUSE
====

About
-----
buse helps managing and executing git commands in several git repositories at the same time.

buse is currently 'work in progress'. Command and arguments could change.

Install
-------

```sh
git clone https://github.com/iamluc/buse.git
cd buse
composer install
```

Use
---

```sh
bin/buse

# or if you have build the phar (see build section)
bin/buse.phar
```

It should display the help message.
To have description of a specific command, just type:
```
$> bin/buse help the_command
```

## status

Get status of your repositories

```
$> bin/buse status
buse: master                                                                                                                     
repo2: master / not clean (1 staged, 3 modified)
repo3: dev / not synchronized (1 ahead, 1 behind)
```

## fetch

Fetch your repositories.

## pull

Pull your repositories.

## push

Push your repositories.

## git

Execute a git command in your repositories.

Note:
- 'git' must not be included in the command
- you must write the command after '--'

```
$> bin/buse git -- log --pretty=oneline -1
buse:
xxxxxxxxxxxx Last commit message buse

repo2:
xxxxxxxxxxxx Last commit message repo2

repo3:
xxxxxxxxxxxx Last commit message repo3
```

## exec

Execute a command in your repositories.

Note:
- you must write the command after '--'

```
$> bin/buse exec -- composer install
buse: | Loading composer repositories with package information
```

## tag

Get and create tags.

## clone

Clone repositories. *This command needs a configuration file `.buse.yml`*

## config

Get and set configuration.

To exclude repositories, update your config typing:

```
$> bin/buse config "global.ignore_repositories" "repo1,repo2"
```

Configuration
-------------

Buse will check if a file `.buse.yml` exists in the current directory.
Note: You can use the `--config` (ie. `buse status --config ~/my-dir`) option to change the directory of the config file.

A basic `.buse.yml` looks like:
```yaml
global:
    ignore_repositories:
        - workshop-serializer-todo
        - twgit

datatheke:
    repositories:
        datatheke: 'git@github.com:datatheke/datatheke.git'
        datatheke-cli: 'git@github.com:datatheke/datatheke-cli.git'

buse:
    repositories:
        buse: 'git@github.com:iamluc/buse.git'

other:
    repositories:
        super-project: ~
```

The `global` section contains the global configuration of buse.

Others sections are called "groups".

If there are "groups" defined in the config file, buse will execute the command for all repositories defined
in groups.

But if buse is called with the `--group` option (ie. `buse status --group datatheke --group buse`),
the command will be executed only for the repositories defined in the selected groups.

Without any "groups" found, buse will search for all git repositories in the working directory.

Even with "groups" in the config file, you can force the search mode using the `--no-group` option.

Build
-----

```sh
composer install --no-dev
bin/compile
```
