# cx

Nx but for Composer

## Installation

```shell
composer require --dev erikgaal/cx
```

## Usage

### Affected

Run tasks in projects affected by changes.

```bash
composer affected -t test
```

| Option            | Description                                          |
|-------------------|------------------------------------------------------|
| `--base`          | Base of the current branch (usually `main`)          |
| `--graph`         | Show the task graph of the command                   |
| `--head`          | Latest commit of the current branch (usually `HEAD`) |
| `--project`, `-p` | Filter certain projects form being processed         |
| `--target`, `-t`  | Tasks to run for affected projects                   |
| `--uncommitted`   | Only uncommitted changes                             |
| `--untracked`     | Only untracked changes                               |

### Graph

Show the dependency graph of the project.

```bash
composer graph
```

| Option           | Description                                          |
|------------------|------------------------------------------------------|
| `--affected`     | Highlight affected projects                          |
| `--base`         | Base of the current branch (usually `main`)          |
| `--head`         | Latest commit of the current branch (usually `HEAD`) |
| `--target`, `-t` | Tasks to run for affected projects                   |
| `--uncommitted`  | Only uncommitted changes                             |
| `--untracked`    | Only untracked changes                               |

### Run many

Run tasks in multiple projects.

```bash
composer run-many -t test
```

| Option            | Description                        |
|-------------------|------------------------------------|
| `--target`, `-t`  | Tasks to run for affected projects |
| `--project`, `-p` | Projects to run                    |
