# Private Composer

This composer plugin adds `github` and `bitbucket` protocols support to Composer in order to simplify [private repositories](https://getcomposer.org/doc/05-repositories.md#using-private-repositories) handling.

Private Composer uses **GitHub** and **BitBucket** APIs to build `packages.json` virtual file on the fly, with all packages from owner's repositories, which can be used in repository type `composer` in `composer.json` file.

## Example

Instead of manually adding each one repository separately to `composer.json` file, e.g.:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url":  "https://github.com/PiotrPress/private-repo-1.git"
    },
    {
      "type": "vcs",
      "url":  "https://github.com/PiotrPress/private-repo-2.git"
    }
  ],
  "require": {
    "piotrpress/private-repo-1": "dev-master",
    "piotrpress/private-repo-2": "*"
  }
}
```

use (in this example) `github` protocol:

```json
{
  "repositories": [
    {
      "type": "composer",
      "url":  "github://PiotrPress"
    }
  ],
  "require": {
    "piotrpress/private-repo-1": "dev-master",
    "piotrpress/private-repo-2": "*"
  }
}
```

## Installation

1. Add the plugin as a global composer requirement:

```shell
$ composer global require piotrpress/private-composer
```

2. Allow the plugin execution:

```shell
$ composer config -g allow-plugins.piotrpress/private-composer true
```

## Authentication

Add GitHub/BitBucket API [authentication](https://getcomposer.org/doc/articles/authentication-for-private-packages.md#http-basic) credentials:

```shell
$ composer config [--global] http-basic.<host> <username> <password>
```

**NOTE:** using `--global` option is recommended to keep credentials outside of project's files.

### GitHub

1. `github.com` example:

```shell
$ composer config --global http-basic.github.com x-oauth-basic token
```

2. Custom domain example:

```shell
$ composer config --global http-basic.example.com x-oauth-basic token
```

- `host` - GitHub's domain, if empty it's equivalent: `github.com`
- `username` - always `x-oauth-basic`
- `password` - GitHub's `token` (generate new one using this [link](https://github.com/settings/tokens/new?scopes=repo&description=Private-Composer))

### BitBucket

1. `bitbucket.org` example:

```shell
$ composer config --global http-basic.bitbucket.org username app_password
```

2. Custom domain example:

```shell
$ composer config --global http-basic.example.com username app_password
```

- `host` - BitBucket's domain, if empty it's equivalent: `bitbucket.org`
- `username` - BitBucket's `username`
- `password` - BitBucket's `app_password` (generate new one using this [link](https://bitbucket.org/account/settings/app-passwords/))

## Usage in `composer.json` file

```json
{
  "repositories": [
    {
      "type": "composer",
      "url":  "<github|bitbucket>://<owner>[@<host>]"
    }
  ]
}
```

### GitHub

1. `github.com` example:

```json
{
  "repositories": [
    {
      "type": "composer",
      "url":  "github://PiotrPress"
    }
  ]
}
```

2. Custom domain example:

```json
{
  "repositories": [
    {
      "type": "composer",
      "url":  "github://PiotrPress@example.com"
    }
  ]
}
```

- `owner` - GitHub's repository `owner`
- `host` - API endpoint domain, if empty it's equivalent: `github.com`

### BitBucket

1. `bitbucket.org` example:

```json
{
  "repositories": [
    {
      "type": "composer",
      "url":  "bitbucket://PiotrPress"
    }
  ]
}
```

2. Custom domain example:

```json
{
  "repositories": [
    {
      "type": "composer",
      "url":  "bitbucket://PiotrPress@example.com"
    }
  ]
}
```

- `owner` - BitBucket's `workspace`
- `host` - API endpoint domain, if empty it's equivalent: `bitbucket.org`

## Usage as a `command`

```shell
$ composer packages <github|bitbucket>://<owner>[@<host>]
```

Command's output is a valid `packages.json` file content.

### Example

```shell
$ composer packages github://PiotrPress > packages.json
```

## Note

If there are many repositories to scan, it may be necessary to increase the process [timeout](https://getcomposer.org/doc/articles/scripts.md#managing-the-process-timeout).

## Requirements

- PHP >= `7.4` version.
- Composer ^`2.0` version.

## License

[MIT](license.txt)