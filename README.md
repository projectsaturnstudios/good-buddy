# GoodBuddy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/projectsaturnagents/good-buddy.svg?style=flat-square)](https://packagist.org/packages/projectsaturnagents/good-buddy)
[![Total Downloads](https://img.shields.io/packagist/dt/projectsaturnstudios/superconductor-core.svg?style=flat-square)](https://packagist.org/packages/projectsaturnstudios/superconductor-core)


A Laravel package for creating AI Agents using local tools? 10-4, Good Buddy!

## Table of Contents

<details><summary>Click to expand</summary><p>

- [Introduction](#introduction)
- [Installation](#installation)

</p></details>

## Introduction

GoodBuddy is the first implementation of Agents that use locally-defined tools. In contrast 
to other Agentic Clients (like Cursor, for example) , that take advantage of an MCP JSON object,
and collectively connect to server, this Agent package makes use off tools
defined in the source code.
It can theoretically use any MCP Tool package by use of
extending Agents\GoodBuddy\Managers\ToolManager in your AppServiceProvider
and the driver extends the Agents\GoodBuddy\Drivers\ToolTime\ToolManagementDriver.

By default, tool management is extends the Superconductor-Core package, an transport-protocol agnostic
implementation of the MCP Protocol. When coupled with the Stremable-HTTP package for
for Superconductor, you can easily generate Tools quickly, and test them with your
Cursor or Claude Desktop Agent to confirm they work, then
spin up Agents, defining the same tools and they will just work.

Require the package with composer
```shell
    composer require projectsaturnagents/good-buddy
```

Install the superconductor core with
```shell
    composer require projectsaturnstudios/superconductor-core
```

You will want to pull in the configuration file:
```shell
    php artisan vendor:publish --tag=agents.good-buddy
```

If you don't already have Superconductor installed, pull in its config:
```shell
    php artisan vendor:publish --tag=mcp
```
# Spinning up agents

To create a new agent run:
```shell
php artisan make:local-agent <name>
```
