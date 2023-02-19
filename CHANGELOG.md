# Changelog

## [1.18.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.18.0...v1.18.1) (2023-02-19)


### Bug Fixes

* **deploy:** fixed admin rights check for optimize (usefull for prod config change reload) ([1fd8ff2](https://github.com/jonathanMelly/pm2etml-intranet/commit/1fd8ff2fab04bb544f072751885dd9779104bfbe))

## [1.18.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.17.0...v1.18.0) (2023-02-19)


### Features

* **sso bridge:** add integrity check of sso request ([927662b](https://github.com/jonathanMelly/pm2etml-intranet/commit/927662bd8ed426861964116dfa500eaca7c0212f))
* **sso bridge:** add throttling to sso AND ask client to get correlationId from bridge ([1b29668](https://github.com/jonathanMelly/pm2etml-intranet/commit/1b29668ba464e3a787fe8dec3f4ebfd4cf27a84c))
* **sso:** add API KEY restriction option AND correlationId generation ([39df0c4](https://github.com/jonathanMelly/pm2etml-intranet/commit/39df0c4c6fd491c1c55711ea89b7293a5cadac84))

## [1.17.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.16.2...v1.17.0) (2022-12-22)


### Features

* **sso-bridge:** correlationId now has TTL (default 10 seconds) ([00e7b6e](https://github.com/jonathanMelly/pm2etml-intranet/commit/00e7b6e28aa2fd1c6f0f8729602372e3e1f5f994))

## [1.16.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.16.1...v1.16.2) (2022-12-22)


### Bug Fixes

* **sso-bridge:** avoid double json_encode of sso data ([0d8e26f](https://github.com/jonathanMelly/pm2etml-intranet/commit/0d8e26f60eb0f003adb2bba48fb45f069fb75b1e)), closes [#61](https://github.com/jonathanMelly/pm2etml-intranet/issues/61)

## [1.16.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.16.0...v1.16.1) (2022-12-22)


### Bug Fixes

* **sso:** array/object state ([5d550e8](https://github.com/jonathanMelly/pm2etml-intranet/commit/5d550e8bf36bfb743dbe62f7edaade720ca488df))

## [1.16.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.15.0...v1.16.0) (2022-12-22)


### Features

* **optimize:** only allow optimize when maintenant or admin ([1b7d2c3](https://github.com/jonathanMelly/pm2etml-intranet/commit/1b7d2c3511262d5721902efcc8175b2a57d12ed6)), closes [#21](https://github.com/jonathanMelly/pm2etml-intranet/issues/21)
* **sso-bridge:** better URI extractor ([d437b8e](https://github.com/jonathanMelly/pm2etml-intranet/commit/d437b8e002cf4363ceb2b062a24f006bf5ef668d))


### Bug Fixes

* **sso-bridge:** check cache key fixed ([4e1961e](https://github.com/jonathanMelly/pm2etml-intranet/commit/4e1961e4b72b20d83e6424d9a5d9b443a9594548))
* **sso-bridge:** split sso-bridge from local auth ([a07f066](https://github.com/jonathanMelly/pm2etml-intranet/commit/a07f0667c4e062bd0d5a3bb39f87481051980c48))
* **sso:** use correct email/username data from o365 and dispatch for bridge ([37fccf6](https://github.com/jonathanMelly/pm2etml-intranet/commit/37fccf66b3f7405cce2f1b33dfb246492e2f2213))

## [1.15.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.14.0...v1.15.0) (2022-12-21)


### Features

* **sso-bridge:** add info log for callback ([56a8c25](https://github.com/jonathanMelly/pm2etml-intranet/commit/56a8c256d1c8093510d0415783e6abe0b9db5013))

## [1.14.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.13.0...v1.14.0) (2022-12-21)


### Features

* **doc:** sso bridge howto ([08b3cdf](https://github.com/jonathanMelly/pm2etml-intranet/commit/08b3cdf6ff88c1150cb512f877855ae4817e1b8c))


### Bug Fixes

* **sso-bridge:** fixed error 500 (cache keyname) + split bridge from local login ([e7b4aef](https://github.com/jonathanMelly/pm2etml-intranet/commit/e7b4aef795de44a0669f106ed6651400ebe40cff))

## [1.13.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.12.0...v1.13.0) (2022-12-21)


### Features

* **deploy:** added some debug info upon cookie process ([78056ef](https://github.com/jonathanMelly/pm2etml-intranet/commit/78056efb84b891946495bc6a6a2212ed2c83b24b))


### Bug Fixes

* **deploy:** get only APP_URL config from .env, not use of it ([78056ef](https://github.com/jonathanMelly/pm2etml-intranet/commit/78056efb84b891946495bc6a6a2212ed2c83b24b))

## [1.12.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.11.1...v1.12.0) (2022-12-21)


### Features

* **deploy:** store get cookie output ([7290d76](https://github.com/jonathanMelly/pm2etml-intranet/commit/7290d76d88f082197435bd7a4d4e4278806bc3a9))

## [1.11.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.11.0...v1.11.1) (2022-12-21)


### Bug Fixes

* **deploy:** fix curl optimize deploy issue ([015d053](https://github.com/jonathanMelly/pm2etml-intranet/commit/015d053bf1e28463182d908fcf8a0f29672ce49a))
* **login:** inform user in cas of unknown account ([fbb80a8](https://github.com/jonathanMelly/pm2etml-intranet/commit/fbb80a8276b9f5542b8b51f0dcc31dfcb386c2b6))

## [1.11.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.10.0...v1.11.0) (2022-12-20)


### Features

* **deploy:** added info on deploy phases for log ([11fd9a0](https://github.com/jonathanMelly/pm2etml-intranet/commit/11fd9a0a56eec098d098ee7ee77fba5fe0699806))

## [1.10.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.9.0...v1.10.0) (2022-12-20)


### Features

* **eval review mail:** send only on weekdays and at 7h45 instead of 5h00 ([33a03e0](https://github.com/jonathanMelly/pm2etml-intranet/commit/33a03e075f1492d80f6cb8d4009e324b6c3deac4))
* **eval:** show project title ([4b69331](https://github.com/jonathanMelly/pm2etml-intranet/commit/4b69331b26f572a226dff58f7bdf97d13f6a5dce)), closes [#44](https://github.com/jonathanMelly/pm2etml-intranet/issues/44)
* **login:** added env flag sso_only to switch login mode ([02029c7](https://github.com/jonathanMelly/pm2etml-intranet/commit/02029c79e6952b165b700c771867ac8b9074a292))
* **sso:** first draft of sso bridge ([1fe4aea](https://github.com/jonathanMelly/pm2etml-intranet/commit/1fe4aea53b5f4dd513efbeedc4fe5d8716ce47cb))


### Bug Fixes

* **logout:** fix o365 logout ([a89f09b](https://github.com/jonathanMelly/pm2etml-intranet/commit/a89f09b1d199f6fe4f3b54863d3f732aa1af7a4d))
* **test:** try to fix evaluationReport email test ([850be8d](https://github.com/jonathanMelly/pm2etml-intranet/commit/850be8d9396f9518118b79c8089218882b28c21c))

## [1.9.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.8.2...v1.9.0) (2022-11-07)


### Features

* **login:** use o365 openid id sso ([0d9a0f4](https://github.com/jonathanMelly/pm2etml-intranet/commit/0d9a0f40f34ee1dd7674aa0186fb0712d8681543))


### Bug Fixes

* **job delete:** escape quotes if needed ([9e49e4b](https://github.com/jonathanMelly/pm2etml-intranet/commit/9e49e4b1290325695ecf01c9f96cdee11cfbed09))

## [1.8.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.8.1...v1.8.2) (2022-11-03)


### Bug Fixes

* **login:** fixed bad getusername 500 error ([a191159](https://github.com/jonathanMelly/pm2etml-intranet/commit/a1911596b687cac979f93577da633c00da252d6d))

## [1.8.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.8.0...v1.8.1) (2022-11-03)


### Bug Fixes

* **password confirmation:** get username from current user (as not passed through form input) ([e07f256](https://github.com/jonathanMelly/pm2etml-intranet/commit/e07f256832c558641b46a20293bcba465ed1e28c))

## [1.8.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.7.0...v1.8.0) (2022-09-29)


### Features

* **auth:** use smtp instead of imap for auth (soon dismissed by microsoft) ([d1c4e97](https://github.com/jonathanMelly/pm2etml-intranet/commit/d1c4e9737ad60f970f0d1f86a214c57c43258bd8))


### Bug Fixes

* **evaluation mail:** 1 mail per client even with multiple clients for 1 contract ([859a6c1](https://github.com/jonathanMelly/pm2etml-intranet/commit/859a6c1ea433681156b63513e2672abca1867666))

## [1.7.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.6.0...v1.7.0) (2022-09-16)


### Features

* **contract:** track evaluations with daily email digest ([2d1b045](https://github.com/jonathanMelly/pm2etml-intranet/commit/2d1b045aa21ef189a6de64a1a5df88f8a227c0bb))
* **evaluation:** force password confirmation with 5 minutes timeout ([6dabe81](https://github.com/jonathanMelly/pm2etml-intranet/commit/6dabe8187030f1bb0170ce81f604bf9f92ff94a7))


### Bug Fixes

* **evaluation:** default evaluation is success ([364c501](https://github.com/jonathanMelly/pm2etml-intranet/commit/364c50140537c562af0dd9735feb7c386dec9f45))

## [1.6.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.5.3...v1.6.0) (2022-09-06)


### Features

* **job:** min periods pass from 30 to 24 ([0a46dc3](https://github.com/jonathanMelly/pm2etml-intranet/commit/0a46dc3d38e2d3eeb287d53c9af9eb3402df4354))


### Bug Fixes

* **job apply:** bad date check for upcoming jobs ([1ac95db](https://github.com/jonathanMelly/pm2etml-intranet/commit/1ac95db85d45f4396b7ca50ec6d5b836bd1b8b45))

## [1.5.3](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.5.2...v1.5.3) (2022-08-29)


### Bug Fixes

* **job load:** job edit keeps periods when < 30 ([ce6476c](https://github.com/jonathanMelly/pm2etml-intranet/commit/ce6476c2e7d7f5938491af29632211cf303aed43))

## [1.5.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.5.1...v1.5.2) (2022-08-26)


### Bug Fixes

* **job edit:** allocated time correctly handled ([db5f52e](https://github.com/jonathanMelly/pm2etml-intranet/commit/db5f52eb2cbcfbee5a1e264530b6d7656fda5797))

## [1.5.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.5.0...v1.5.1) (2022-08-22)


### Bug Fixes

* **user sync:** better output for diff and look only at 1st sheet ([6ac54f1](https://github.com/jonathanMelly/pm2etml-intranet/commit/6ac54f1c6d44ea5e4c6bffb7069d166d7e50bb4a))

## [1.5.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.4.1...v1.5.0) (2022-08-22)


### Features

* **user sync:** handle deleted/restored users ([fa8979b](https://github.com/jonathanMelly/pm2etml-intranet/commit/fa8979b3a2bd565919cf81bd305475fd822b1732))
* **user:** added period and class infos ([069f979](https://github.com/jonathanMelly/pm2etml-intranet/commit/069f97950b1bf7f10a08ce81ace8faa1ff56f08b))

## [1.4.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.4.0...v1.4.1) (2022-08-12)


### Bug Fixes

* **job filter:** xp and priority filters restored ([ef361bb](https://github.com/jonathanMelly/pm2etml-intranet/commit/ef361bb9bfb891fbbdc6431cc86e12512e45fff9))

## [1.4.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.3.0...v1.4.0) (2022-08-12)


### Features

* **job drafts:** drafts are shown and can be filtered ([6866793](https://github.com/jonathanMelly/pm2etml-intranet/commit/6866793631afd8ff0b5830f59f8365b276083304))
* **job:** Allow any teacher to be the client ([0be0542](https://github.com/jonathanMelly/pm2etml-intranet/commit/0be054278e0c2767ce6971a1532da87d61df7e0b))
* **job:** Cursor help on oneshot tooltip ([8680889](https://github.com/jonathanMelly/pm2etml-intranet/commit/86808898895ab837d3f3400c6d70b36a48a11922))
* **job:** Separator for skills + better pointer ([9512fdf](https://github.com/jonathanMelly/pm2etml-intranet/commit/9512fdfa62d46a7b2e35b57609869d99a02f45d6))
* **marketplace:** add meteor icon for oneshot ([a3b4a28](https://github.com/jonathanMelly/pm2etml-intranet/commit/a3b4a285d15669f64871c60b7dc58c9617d2b566))
* **marketplace:** add title to skills list ([a04d9cf](https://github.com/jonathanMelly/pm2etml-intranet/commit/a04d9cf90911b698bb43f57a07c01a94e3d1287f))
* **marketplace:** click on provider filters jobs ([bf8c5c5](https://github.com/jonathanMelly/pm2etml-intranet/commit/bf8c5c537017b2a6b463c0aceb1152cdcb061eaf))


### Bug Fixes

* **job edit:** oneshot property is properly updated ([779241f](https://github.com/jonathanMelly/pm2etml-intranet/commit/779241fff0fd51c1a86cb6743fb5fd550e9e354a))
* **job:** Providers title more visible ([e21ee43](https://github.com/jonathanMelly/pm2etml-intranet/commit/e21ee4354623588841ecdb6416c87b84b606c174))
* **marketplace:** better visibility of job add button ([fff5adf](https://github.com/jonathanMelly/pm2etml-intranet/commit/fff5adf2fb7a1be81e2939588eedffcd539156e6))

## [1.3.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.2.1...v1.3.0) (2022-06-29)


### Features

* **marketplace:** add counter to show how many jobs are listed ([69d6cee](https://github.com/jonathanMelly/pm2etml-intranet/commit/69d6cee42b8e22edeb4fccf4d48583b0fa7b539a)), closes [#6](https://github.com/jonathanMelly/pm2etml-intranet/issues/6)


### Bug Fixes

* **version:** add space for dev ([2d47839](https://github.com/jonathanMelly/pm2etml-intranet/commit/2d47839e44966c482d888de0ec11b3a5f43725a4))

## [1.2.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.2.0...v1.2.1) (2022-06-27)


### Bug Fixes

* **version:** shows correct version of app in all environments ([ab70fc1](https://github.com/jonathanMelly/pm2etml-intranet/commit/ab70fc14f365339e700513cec368b5ccda6a260c))

## [1.2.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.1.1...v1.2.0) (2022-06-26)


### Features

* **footer:** add github icon+link and use new window for other links ([18e4262](https://github.com/jonathanMelly/pm2etml-intranet/commit/18e4262efce4d8beddf8b9edc108385c8354a2d0))
* **users:** ability to sync users with their groups (teacher or student) ([3bcde22](https://github.com/jonathanMelly/pm2etml-intranet/commit/3bcde22cc37679ee140085518f9f3e4bb0a56516))

## [1.1.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.1.0...v1.1.1) (2022-06-11)


### Bug Fixes

* **deploy:** prod version only shows version (not staging...) ([f4acb40](https://github.com/jonathanMelly/pm2etml-intranet/commit/f4acb40c07f55f1bb314f2cc96bbaec17fd4d80a))

## [1.1.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.0.0...v1.1.0) (2022-06-11)


### Features

* **markeplace:** jobs can be filtered ([b9973b0](https://github.com/jonathanMelly/pm2etml-intranet/commit/b9973b07c7d3e6409e5e9b182ee3eb6d7c290c6d))
* **version:** add link to release info footer ([a83edf5](https://github.com/jonathanMelly/pm2etml-intranet/commit/a83edf55acac8023bcc11b50265fd4f07fbca63d))


### Bug Fixes

* **readme:** update staging badge ([9027d79](https://github.com/jonathanMelly/pm2etml-intranet/commit/9027d794ff1b62ff40f67868525b8d7aa512a23b))

## 1.0.0 (2022-06-10)


### Features

* **First steps:** O365 auth, job CRUD (marketplace), contract start,evaluate,delete
