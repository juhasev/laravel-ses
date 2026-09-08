# Changelog

All notable changes to `laravel-ses` will be documented in this file.

## 8.1.0 - 2026-09-08

#### Upgrade note

If you extend `SesMailer` or `SesMailFake` and override `sendSymfonyMessage()` or `send()`, update their return types: they now return `?Symfony\Component\Mailer\SentMessage` and `?Illuminate\Mail\SentMessage` respectively, instead of `void`.

### What's Changed

* Return the SentMessage from SesMail::send() by @leMaur in https://github.com/juhasev/laravel-ses/pull/45

**Full Changelog**: https://github.com/juhasev/laravel-ses/compare/8.0.1...8.1.0

## 8.0.1 - 2026-09-08

### What's Changed

* Harden the supply chain by @leMaur in https://github.com/juhasev/laravel-ses/pull/37
* Declare the illuminate components the package extends by @leMaur in https://github.com/juhasev/laravel-ses/pull/43
* Allow voku/simple_html_dom 5.0 by @leMaur in https://github.com/juhasev/laravel-ses/pull/44
* Bump the workflow actions to their current majors by @leMaur in https://github.com/juhasev/laravel-ses/pull/46

**Full Changelog**: https://github.com/juhasev/laravel-ses/compare/8.0.0...8.0.1

## 8.0.0 - 2026-09-08

### What's Changed

* Add Laravel 13 support by @leMaur in https://github.com/juhasev/laravel-ses/pull/36

**Full Changelog**: https://github.com/juhasev/laravel-ses/compare/7.0.0...8.0.0

## 7.0.0 - 2026-09-08

### What's Changed

* Add Laravel 12 support by @leMaur in https://github.com/juhasev/laravel-ses/pull/35

**Full Changelog**: https://github.com/juhasev/laravel-ses/compare/6.0.1...7.0.0

## 6.0.1 - 2026-05-22

### What's Changed

* chore: update workflow to use 'master' branch instead of 'main'

**Full Changelog**: https://github.com/juhasev/laravel-ses/compare/v6.0.0...6.0.1
