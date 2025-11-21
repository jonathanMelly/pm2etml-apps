# Changelog

## [1.57.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.56.0...v1.57.0) (2025-11-21)


### Features

* **contract:** better dispatch detection ([5de07e3](https://github.com/jonathanMelly/pm2etml-apps/commit/5de07e359679b2c810615e976304bd1080353902))
* **contract:** provider can add an arbitrary contract ([97b35cc](https://github.com/jonathanMelly/pm2etml-apps/commit/97b35cc4c7700886ad9dc8d14a0ea11765f92fce))
* **eval:** auto pdfs dispatcher ([932291b](https://github.com/jonathanMelly/pm2etml-apps/commit/932291b8911f1e09c1c40733c4d1cea80d5f6f67))


### Bug Fixes

* **contract:** avoid errors on already moved pending attachments ([df29c9c](https://github.com/jonathanMelly/pm2etml-apps/commit/df29c9c155c8f63d30e739af448b2227d09a0e74))
* **contract:** dispatch js es6 to standard ([75bb092](https://github.com/jonathanMelly/pm2etml-apps/commit/75bb0929c66d50f69b1279aa2f89ff0dcedbb686))

## [1.56.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.55.1...v1.56.0) (2025-11-06)


### Features

* **marketplace status filter:** added trashed status ([01fe8eb](https://github.com/jonathanMelly/pm2etml-apps/commit/01fe8eb4a387d1525fb92c4f329f2adccc6430a8))

## [1.55.1](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.55.0...v1.55.1) (2025-11-04)


### Bug Fixes

* **student evaluation download:** filename corresponds to original ([c6a3697](https://github.com/jonathanMelly/pm2etml-apps/commit/c6a36972c5fd57096d3e5cd5d8164bc6177a2058))

## [1.55.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.54.1...v1.55.0) (2025-09-15)


### Features

* **eval:** possibilité d'attacher un fichier PDF pour une éval (visible par l'élève) ([c454143](https://github.com/jonathanMelly/pm2etml-apps/commit/c45414360db90a1899ce91182fc4f08e7d10d2ef))


### Bug Fixes

* **academic period:** do not cache empty periods ([0935de3](https://github.com/jonathanMelly/pm2etml-apps/commit/0935de3fa35074b58a6e45801550785c4e0585be))
* **i18n scan:** better handle custom translation patterns ([280fd3b](https://github.com/jonathanMelly/pm2etml-apps/commit/280fd3bb14d3a59f5f77a0a9bd5d86eea9a27157))

## [1.54.1](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.54.0...v1.54.1) (2025-08-26)


### Bug Fixes

* **student dashboard:** no more error for old contracts with teachers that have left ([98e3894](https://github.com/jonathanMelly/pm2etml-apps/commit/98e3894e4ba750ac964b49f9e27e01def98f41ae))

## [1.54.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.53.2...v1.54.0) (2025-06-28)


### Features

* **export excel:** added total periods ([244ddf9](https://github.com/jonathanMelly/pm2etml-apps/commit/244ddf9ea192372aaf3cc6bcf32661d7d106b2d6))
* **nightwatch:** added auditing module (for dev only now) ([e953182](https://github.com/jonathanMelly/pm2etml-apps/commit/e9531822bfb7930705a569187d82816144939232))
* **test:** added test shortcut to remind of building assets first... ([e5e354b](https://github.com/jonathanMelly/pm2etml-apps/commit/e5e354b39b5d4a2ca5af2d9bc67a05fa7fdcb1b2))


### Bug Fixes

* **evaluation report:** also include student details from trash for report on students that have quited ([c118751](https://github.com/jonathanMelly/pm2etml-apps/commit/c118751e8b09571b55fd8eaa91ca1d14a96c3c31))
* **export excel:** remove extra parentheses in headers ([e297118](https://github.com/jonathanMelly/pm2etml-apps/commit/e29711845359cf0c5badda84330c5594a0367eb8))
* **psr-4:** ClientContractsEditFormTest.php has correct namespace ([d865bfe](https://github.com/jonathanMelly/pm2etml-apps/commit/d865bfedd51def47f3ef64cdd8dc504662c58cdc))

## [1.53.2](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.53.1...v1.53.2) (2025-06-21)


### Bug Fixes

* **evaluation report:** include students that have quited to purge data ([7789f6d](https://github.com/jonathanMelly/pm2etml-apps/commit/7789f6ddae1097daac6947ee20a9eeb7fa9b4c68))

## [1.53.1](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.53.0...v1.53.1) (2025-05-26)


### Bug Fixes

* **filter:** correctly filter projects with more than 150p ([a4309c3](https://github.com/jonathanMelly/pm2etml-apps/commit/a4309c33d62e698e7b4e540a170df0943f44f91c))

## [1.53.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.52.3...v1.53.0) (2025-01-20)


### Features

* **create job mask:** grouped particularities ([030dcde](https://github.com/jonathanMelly/pm2etml-apps/commit/030dcde8781059c1d2d109514ce44481e96260d8))

## [1.52.3](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.52.2...v1.52.3) (2025-01-20)


### Bug Fixes

* **jobApplication:** added missing js in build ([154afd4](https://github.com/jonathanMelly/pm2etml-apps/commit/154afd41041e22450771169458acb6f03b42c122))

## [1.52.2](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.52.1...v1.52.2) (2025-01-17)


### Bug Fixes

* **null image:** simple fail-safe ([988c9c3](https://github.com/jonathanMelly/pm2etml-apps/commit/988c9c3cf59f7411c2b2ba6ffb906897b46e4f9c))

## [1.52.1](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.52.0...v1.52.1) (2025-01-17)


### Bug Fixes

* **git fetch:** disable cert check as missing on elara ([d1d0f63](https://github.com/jonathanMelly/pm2etml-apps/commit/d1d0f63951f19b2c96e507d7444329e18cfa6fa5))
* **null image:** try to fix strange null images ??? ([fc72e64](https://github.com/jonathanMelly/pm2etml-apps/commit/fc72e6424ef57da4a527272d398a0078d6d05c30))

## [1.52.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.51.0...v1.52.0) (2025-01-12)


### Features

* **remediation:** student can request a remediation that is validated (or refused) by the client... Evaluation can then be changed ([1066ba8](https://github.com/jonathanMelly/pm2etml-apps/commit/1066ba8b1b4ccd8f5730c432fa9b2891ac5efdac))


### Bug Fixes

* **app name:** remove traces of intranet ([67cd1b4](https://github.com/jonathanMelly/pm2etml-apps/commit/67cd1b49a6119dbb89d55be7e264d89492df7842))

## [1.51.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.50.0...v1.51.0) (2025-01-06)


### Features

* **application:** Allow the worker to enter wish priority ([f8be28f](https://github.com/jonathanMelly/pm2etml-apps/commit/f8be28f9b77dda80d4b4e32d23af404b22a48ed9))
* **application:** Perform project allocation ([9ece71b](https://github.com/jonathanMelly/pm2etml-apps/commit/9ece71bcc998e4b1aa18799375c0407e07108797))
* **application:** Provide a button to teachers who have pending applications ([9be791d](https://github.com/jonathanMelly/pm2etml-apps/commit/9be791dc57f0e082585c31194b011c77ef86c181))
* **application:** Resign from job ([f2c89d4](https://github.com/jonathanMelly/pm2etml-apps/commit/f2c89d49d61a486f00d24388c6ec0a8a5b1af243))
* **application:** Show popup for project allocation ([fda5a34](https://github.com/jonathanMelly/pm2etml-apps/commit/fda5a34559789404b4c4b955d0dec3609d438a51))
* **application:** Show the applicant/job matrix ([2ce512e](https://github.com/jonathanMelly/pm2etml-apps/commit/2ce512e21f024a121b28361e1ec200db8982e80f))
* **hire handling:** only allow to teachers ([b570756](https://github.com/jonathanMelly/pm2etml-apps/commit/b570756a57d03051e79cc4a9c33701de938045c4))
* **job def:** Add the 'by_application' field to job definition ([292848c](https://github.com/jonathanMelly/pm2etml-apps/commit/292848c69796d3c0936eb5fa007404cd40a6de99))


### Bug Fixes

* **applications:** Don't crash when all jobs have been allocated ([3e10fc4](https://github.com/jonathanMelly/pm2etml-apps/commit/3e10fc493ca8412cc4fffdcacb157af199e7b53e))
* **axios:** use non affected by security issue version ([8792962](https://github.com/jonathanMelly/pm2etml-apps/commit/87929628f473d67228ba26f708d53413ef87bc89))
* **hire:** fixed filename ([a668186](https://github.com/jonathanMelly/pm2etml-apps/commit/a668186010908d22e77bf3259672e9f0b838c596))
* **npm:** added vue missing ts ([455c761](https://github.com/jonathanMelly/pm2etml-apps/commit/455c761997c0b5a1c0d69cc5341983e5077a7960))
* **wish priority:** Save the value entered by the user ;) ([c376349](https://github.com/jonathanMelly/pm2etml-apps/commit/c376349c6db2c8a7d09ea8972913f04c09d07cb7))

## [1.50.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.49.0...v1.50.0) (2024-11-29)


### Features

* **logs:** add pail to help with logs ([15b0d20](https://github.com/jonathanMelly/pm2etml-apps/commit/15b0d208b8467478cad7e5ef42c8e5995cf3a504))
* **sso:** switched check log in debug ([dc699cd](https://github.com/jonathanMelly/pm2etml-apps/commit/dc699cdb73ee5d8b24f644da3b5151c0b574218a))
* **worker contracts:** show past contracts ([4b14d5e](https://github.com/jonathanMelly/pm2etml-apps/commit/4b14d5ebf1be98e1c219e2216fc32b375e86a19e))


### Bug Fixes

* **names:** added one letter to last name ([deb9bd5](https://github.com/jonathanMelly/pm2etml-apps/commit/deb9bd5b5387847b055923fb4d4d191e9dae4d46))

## [1.49.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.48.3...v1.49.0) (2024-11-20)


### Features

* **sso:** added log for sso bridge check ([4326232](https://github.com/jonathanMelly/pm2etml-apps/commit/432623257e59bd613173bcde4b8c0011ac699be8))

## [1.48.3](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.48.2...v1.48.3) (2024-11-19)


### Bug Fixes

* **download:** try to get nice looking filename back when downloading ([bcc62ae](https://github.com/jonathanMelly/pm2etml-apps/commit/bcc62ae169063d7cf8590ff7c8b482ffc247ce32))

## [1.48.2](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.48.1...v1.48.2) (2024-11-04)


### Bug Fixes

* **nomad student:** log info for nomad students ([6e6d100](https://github.com/jonathanMelly/pm2etml-apps/commit/6e6d1001154ee2bbd1573c8df6d648b6821f9e3d))

## [1.48.1](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.48.0...v1.48.1) (2024-10-30)


### Bug Fixes

* **deploy:** fix missing vitepress cache content ([02c185e](https://github.com/jonathanMelly/pm2etml-apps/commit/02c185e2c2366e33c6c91353f33582e004dfaaa8))

## [1.48.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.47.3...v1.48.0) (2024-10-30)


### Features

* **bridge:** allow multiple api keys ([056e787](https://github.com/jonathanMelly/pm2etml-apps/commit/056e787be0a6b00f2605e10270f1e72516117692))

## [1.47.3](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.47.2...v1.47.3) (2024-08-28)


### Bug Fixes

* **mp:** if teacher is not master it’s ok... ([c36ac5c](https://github.com/jonathanMelly/pm2etml-apps/commit/c36ac5ca4f6016a6585ca14abd7956e5434a1674))

## [1.47.2](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.47.1...v1.47.2) (2024-08-28)


### Bug Fixes

* **warning no groupid:** only for students ([9c2a390](https://github.com/jonathanMelly/pm2etml-apps/commit/9c2a3907fc969666535bfdc8cca5f4bb57019fae))

## [1.47.1](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.47.0...v1.47.1) (2024-08-28)


### Bug Fixes

* **mp providers:** add 3 letters of name +dynamic acronym ([3841e85](https://github.com/jonathanMelly/pm2etml-apps/commit/3841e85fab9e288d698274a5a1cb674a7394d535))
* **mp providers:** add 3 letters of name +dynamic acronym ([f1136fe](https://github.com/jonathanMelly/pm2etml-apps/commit/f1136fe830e79f83f3cc33fffab82caac9fa44ea))
* **remaining time:** round ([6b5f71b](https://github.com/jonathanMelly/pm2etml-apps/commit/6b5f71b46a7ce69da0f0e74e1b6f5c496fd1b587))
* **remaining time:** round ([77b4d25](https://github.com/jonathanMelly/pm2etml-apps/commit/77b4d2523ac01f44ef29fd97b5f3b6ad5daf6f99))
* **summaries:** do not crash if missing groupmember ([9ec7bfc](https://github.com/jonathanMelly/pm2etml-apps/commit/9ec7bfcadf17ccd92ca2ed491ef9ab4119898cbb))
* **summaries:** do not crash if missing groupmember ([9edd208](https://github.com/jonathanMelly/pm2etml-apps/commit/9edd2083474e5f9c093d14c693006e151837af58))

## [1.47.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.46.0...v1.47.0) (2024-07-03)


### Features

* **apps:** integrated app footer for apps ([5a1c50d](https://github.com/jonathanMelly/pm2etml-apps/commit/5a1c50df92282b007a270eea013f00ef85f48df4))
* **mail:** added info log for mail evaluation report ([53f88d8](https://github.com/jonathanMelly/pm2etml-apps/commit/53f88d83b0f5083951c06e7c1fff20a56f4a7d64))


### Bug Fixes

* **login:** add autocomplete attribute for username ([e2d4536](https://github.com/jonathanMelly/pm2etml-apps/commit/e2d4536cbeb9523d842c9bea075019f2a81127a7))

## [1.46.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.45.0...v1.46.0) (2024-06-26)


### Features

* **dashboard:** improve perf on contracts loading ([0ba57bf](https://github.com/jonathanMelly/pm2etml-apps/commit/0ba57bf7a3d1b026eba380e04a797074c09fe836))

## [1.45.0](https://github.com/jonathanMelly/pm2etml-apps/compare/v1.44.0...v1.45.0) (2024-06-17)


### Features

* **laravel:** finish update to laravel 11 ([2a4dc25](https://github.com/jonathanMelly/pm2etml-apps/commit/2a4dc25a7142893dfeaa4a432aa503dab2cdf199))

## [1.44.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.43.0...v1.44.0) (2024-06-13)


### Features

* **echarts:** reduce echarts js size (+refactor) ([9e488ec](https://github.com/jonathanMelly/pm2etml-intranet/commit/9e488ecf4d091181dff4221a0c7093266eea0859))
* **sso-bridge:** update url ([45dd6e1](https://github.com/jonathanMelly/pm2etml-intranet/commit/45dd6e1723abb6002f0e850cbd8854b6f5da82e8))

## [1.43.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.42.3...v1.43.0) (2024-04-03)


### Features

* **dashboard filters:** auto hide project if no contracts inside ([2e49cfc](https://github.com/jonathanMelly/pm2etml-intranet/commit/2e49cfcb672be9008287f2c8abeba143d56182f5))


### Bug Fixes

* **group filter:** auto-wrap when there are a lot ([599e8c2](https://github.com/jonathanMelly/pm2etml-intranet/commit/599e8c22073cc143755e6e95a39edbfe87f2a866))
* **manual apply:** non admin teacher can manually add a contract (no more 403) ([a51e210](https://github.com/jonathanMelly/pm2etml-intranet/commit/a51e210554de6c5fc167c0cb3d796dc78745f3b5))

## [1.42.3](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.42.2...v1.42.3) (2024-03-25)


### Bug Fixes

* **missing cid:** remove id from message title error ([0711093](https://github.com/jonathanMelly/pm2etml-intranet/commit/07110934a92f791f31c66afd717ea87cc3b89947))

## [1.42.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.42.1...v1.42.2) (2024-03-20)


### Bug Fixes

* **class filters:** ok for non "prof de classe" people ([411d7a5](https://github.com/jonathanMelly/pm2etml-intranet/commit/411d7a561f2bddc5f2de3ba091c658d4a977aba3))

## [1.42.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.42.0...v1.42.1) (2024-03-18)


### Bug Fixes

* **bad cid:** generic exception to be filtered in sentry... (details are in the sentry details) ([6ebe4ae](https://github.com/jonathanMelly/pm2etml-intranet/commit/6ebe4ae22f3bf818dd9a1daa8b97acc25a91c930))

## [1.42.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.41.0...v1.42.0) (2024-03-17)


### Features

* **attachment:** upgrade max size to 23Mo instead of 10 ([147b116](https://github.com/jonathanMelly/pm2etml-intranet/commit/147b116d64b666795adca266e842683a25577c8a))

## [1.41.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.40.1...v1.41.0) (2024-03-14)


### Features

* **contracts:** filter by class ([37f3b8c](https://github.com/jonathanMelly/pm2etml-intranet/commit/37f3b8c0eaf4bf6cfc8932559a06e8397421ed76))

## [1.40.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.40.0...v1.40.1) (2024-03-06)


### Bug Fixes

* **charts:** pie group title is not y misplaced when students &lt;8 ([eecf499](https://github.com/jonathanMelly/pm2etml-intranet/commit/eecf4997e42f425c69ea4c431544fc3832368f6f))
* **charts:** pies are shown completely (not missing last part because of wrong height) ([71cde8e](https://github.com/jonathanMelly/pm2etml-intranet/commit/71cde8e5a0e0344ef4a2800cda4220696a82b959))

## [1.40.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.39.0...v1.40.0) (2024-02-01)


### Features

* **repetition:** auto removes contracts for students that repeat (redoublement) ([a374f26](https://github.com/jonathanMelly/pm2etml-intranet/commit/a374f260ed193d0d7914a6ce700a770df82dc6f5))
* **users cleanup:** report fixes ([fc53322](https://github.com/jonathanMelly/pm2etml-intranet/commit/fc53322fab3be858200abe89efaa3768bf4cf779))
* **users import:** added id of impacted users ([3c6248b](https://github.com/jonathanMelly/pm2etml-intranet/commit/3c6248bbb063c26c482993c46160ef89edc7bf0b))

## [1.39.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.38.0...v1.39.0) (2024-01-31)


### Features

* **data:** handle/cleanup student inconsistency (having 2 groups in same year...) ([bee2bab](https://github.com/jonathanMelly/pm2etml-intranet/commit/bee2bab123fed15b8ac80cf98ef450c085066427))
* **data:** handle/cleanup student inconsistency (having 2 groups in same year...) ([ef7c50e](https://github.com/jonathanMelly/pm2etml-intranet/commit/ef7c50e020df6d652c3d2730f97b5e04f3823238))

## [1.38.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.37.0...v1.38.0) (2024-01-26)


### Features

* **contract:** client (teachers) can now add arbitrary contracts for any worker (student) if needed ([776ae5a](https://github.com/jonathanMelly/pm2etml-intranet/commit/776ae5a27322c28bb127b8a278705a04bcc59b62))
* **toast:** increase show delay from 5 to 30seconds ([f8a90a0](https://github.com/jonathanMelly/pm2etml-intranet/commit/f8a90a0c1971fcab1f35c36fbd36ebde1ead5b74))


### Bug Fixes

* **bulk edit:** fix save button css ([8aa8d22](https://github.com/jonathanMelly/pm2etml-intranet/commit/8aa8d22881c140009a155cbf38f7aaccaa5cd2a4))
* **contract eval:** button css more visible ([aa7da71](https://github.com/jonathanMelly/pm2etml-intranet/commit/aa7da7123fc72b1c626eac33e2429457345f0e9b))
* **contract:** bulk edit honores any modification done (no more unclosed transaction levels...) ([0912608](https://github.com/jonathanMelly/pm2etml-intranet/commit/0912608ce03d90524af63cc27c4395c570e978c7))
* **delete contract:** show subpart info ([cd52701](https://github.com/jonathanMelly/pm2etml-intranet/commit/cd527017b1dfdb953347c8965803102e444510df))

## [1.37.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.36.2...v1.37.0) (2024-01-18)


### Features

* **cache:** allow cache clear from web request for admin /deploy/clearCache ([6aeff71](https://github.com/jonathanMelly/pm2etml-intranet/commit/6aeff71e1b3b5c2180788355971e5ade763d4c9b))
* **worker:** ability to switch client (if not yet evaluated) ([d56a193](https://github.com/jonathanMelly/pm2etml-intranet/commit/d56a1932a31409f1e0b068ad29e6e32760a53670))


### Bug Fixes

* **contract:** allow bulk edit multi-parts contracts instead of printing an error ([d24ba93](https://github.com/jonathanMelly/pm2etml-intranet/commit/d24ba937dd680c8d43b22a5f9726f9b700e51c0e))
* **ui:** flash message in one row instead of 2 (daisyui upgrade...) ([5aca238](https://github.com/jonathanMelly/pm2etml-intranet/commit/5aca2381286c2104ef1add1c31dfb54808e9641a))
* **ui:** job apply fields alignment ([225fb69](https://github.com/jonathanMelly/pm2etml-intranet/commit/225fb69077223204a0b73747c05076b7ca248a09))

## [1.36.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.36.1...v1.36.2) (2024-01-17)


### Bug Fixes

* **eval charts:** success when &gt;=80% (not strictly >80%) ([74ab064](https://github.com/jonathanMelly/pm2etml-intranet/commit/74ab064c8aa4b9b4269b824da34ed11c91643d38))

## [1.36.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.36.0...v1.36.1) (2024-01-11)


### Bug Fixes

* **eval report:** handles deleted contracts correctly ([7477c28](https://github.com/jonathanMelly/pm2etml-intranet/commit/7477c2864dddbc4b9db162824d2021c78f88ef48))

## [1.36.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.35.0...v1.36.0) (2023-11-27)


### Features

* **eval export:** add clients in project header ([212064d](https://github.com/jonathanMelly/pm2etml-intranet/commit/212064db5aa9dfc31b8220dc8b60dae2faf8d1fb))

## [1.35.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.34.0...v1.35.0) (2023-11-25)


### Features

* **eval export:** add specific grade info in excel comment ([f472bea](https://github.com/jonathanMelly/pm2etml-intranet/commit/f472bea863cda82ae129adfd94c28179387edf27))
* **eval export:** merge all grades for same project ([f472bea](https://github.com/jonathanMelly/pm2etml-intranet/commit/f472bea863cda82ae129adfd94c28179387edf27))

## [1.34.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.33.1...v1.34.0) (2023-11-24)


### Features

* **contract edit:** add subpart info (for multi-eval on same project...) ([6748346](https://github.com/jonathanMelly/pm2etml-intranet/commit/6748346117f0116a68b3feacf23625a905e3b547))
* **contract:** add localstorage persisted already evaluated filter toggle ([a78fc8c](https://github.com/jonathanMelly/pm2etml-intranet/commit/a78fc8c6ef6dad80182cb71073264120fdfacf4c))
* **evaluation summary:** collapse by default evaluation summary (status is saved in localstorage) ([a78fc8c](https://github.com/jonathanMelly/pm2etml-intranet/commit/a78fc8c6ef6dad80182cb71073264120fdfacf4c))

## [1.33.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.33.0...v1.33.1) (2023-11-22)


### Bug Fixes

* **evaluation log:** fix sql trigger syntax on condition !=null =&gt; is not null, which reactivates mail report... ([9d068e3](https://github.com/jonathanMelly/pm2etml-intranet/commit/9d068e3f1ee3278e8461a7b12098738c4880f87a))

## [1.33.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.32.2...v1.33.0) (2023-11-08)


### Features

* **formative jobs:** allow 0 period contracts ([a187130](https://github.com/jonathanMelly/pm2etml-intranet/commit/a18713035f5ec367fce1289c3b990efb1abce33a))

## [1.32.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.32.1...v1.32.2) (2023-11-07)


### Bug Fixes

* **eval export:** prints correct title and periods for aggregated projects in excel export ([2a15290](https://github.com/jonathanMelly/pm2etml-intranet/commit/2a152905cbe96aa25eb891582a9b5d1710bd8046))
* **eval export:** removed extra dash after project subpart (if any) ([59e9f72](https://github.com/jonathanMelly/pm2etml-intranet/commit/59e9f7262811ef2742650c3fd91976c729e34efe))

## [1.32.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.32.0...v1.32.1) (2023-11-06)


### Bug Fixes

* **export:** print correct periods count for project (not accumulated...) ([455fbe0](https://github.com/jonathanMelly/pm2etml-intranet/commit/455fbe05500b7a7c162a2640f3b1cb8c7cb67fa7))

## [1.32.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.31.0...v1.32.0) (2023-09-28)


### Features

* **job:** add warning about contracts not deleted when deleting a job ([761a7b9](https://github.com/jonathanMelly/pm2etml-intranet/commit/761a7b9196b380f344fec72753072bd1e40714d5))


### Bug Fixes

* **dashboard:** no more error on contracts linked to deleted jobs ([7f277bd](https://github.com/jonathanMelly/pm2etml-intranet/commit/7f277bd0964eb01ef99506917d4ed9975a5c3578))
* **dashboard:** show active contracts of deleted jobs too ([761a7b9](https://github.com/jonathanMelly/pm2etml-intranet/commit/761a7b9196b380f344fec72753072bd1e40714d5))

## [1.31.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.30.0...v1.31.0) (2023-09-08)


### Features

* **contract:** ability to edit contract periods (more than only edit dates) ([b376936](https://github.com/jonathanMelly/pm2etml-intranet/commit/b3769364c87ec7047ede09928e865315ef1aeb2f))

## [1.30.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.29.0...v1.30.0) (2023-09-06)


### Features

* **contracts:** auto expand projects with pending evaluations ([e0f7f94](https://github.com/jonathanMelly/pm2etml-intranet/commit/e0f7f942c21fb90ca468d701d394d8a9233c5c77))
* **icescrum:** add link to icescrub tools ([05468b1](https://github.com/jonathanMelly/pm2etml-intranet/commit/05468b1c1299c9e496d1587acc60c11c522d8193))


### Bug Fixes

* **contract:** deleting contract does not trigger a wrong error message AND user rights are checked ([3b5d33b](https://github.com/jonathanMelly/pm2etml-intranet/commit/3b5d33b8361be699464fbb2aa61818ddfedfdc71))
* **contracts:** only show project contracts of current period ([5aae2e4](https://github.com/jonathanMelly/pm2etml-intranet/commit/5aae2e417bf345c350327fae4a533d5457122ff0))
* **job:** remove useless "action" column ([1913022](https://github.com/jonathanMelly/pm2etml-intranet/commit/1913022789ab2bd33d07ead5aa9cdf904a3f46b3))
* **stats:** also show stats for standard clients (no special role) ([55a1556](https://github.com/jonathanMelly/pm2etml-intranet/commit/55a1556a7e721dc59897b6eb16e04bbea4f1e5ba))

## [1.29.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.28.2...v1.29.0) (2023-08-25)


### Features

* **job:** allow .sql attachment ([a997cc6](https://github.com/jonathanMelly/pm2etml-intranet/commit/a997cc6c5a3504a29b6e5529a2b84e4e0b78e82c))

## [1.28.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.28.1...v1.28.2) (2023-08-25)


### Bug Fixes

* **contract:** also soft delete associated worker_contract ([fee682c](https://github.com/jonathanMelly/pm2etml-intranet/commit/fee682ca88b8d70f705e9edf784a1c3e925e3882))

## [1.28.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.28.0...v1.28.1) (2023-08-22)


### Bug Fixes

* **sso:** better invalid correlationId report ([8b99029](https://github.com/jonathanMelly/pm2etml-intranet/commit/8b9902923e38798234aac5784acbfea237598dd6))

## [1.28.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.27.0...v1.28.0) (2023-08-20)


### Features

* **ui:** better look for contract apply with multiple evals ([6b877f4](https://github.com/jonathanMelly/pm2etml-intranet/commit/6b877f4b29a4262749faf51c5e78d061dda7d3ff))

## [1.27.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.26.0...v1.27.0) (2023-08-18)


### Features

* **contracts:** handles multiple subcontracts per project contract (enable easy multiple evaluations for 1 project) ([e1f3531](https://github.com/jonathanMelly/pm2etml-intranet/commit/e1f3531a3a3a479560ccd4a9392a5a3fcd4ae68f))

## [1.26.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.25.1...v1.26.0) (2023-08-06)


### Features

* **tools:** added icescrum in tools (and link to doc) ([0c0c415](https://github.com/jonathanMelly/pm2etml-intranet/commit/0c0c415f176be720e4edb63a5fa54256eb274e58))


### Bug Fixes

* **test:** migrator test user forced to 2022 period ([6df825b](https://github.com/jonathanMelly/pm2etml-intranet/commit/6df825b88c59cb633496c166fee880b94c5d0e7f))

## [1.25.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.25.0...v1.25.1) (2023-06-27)


### Bug Fixes

* **export:** remove duplicated projects ([225810e](https://github.com/jonathanMelly/pm2etml-intranet/commit/225810e2036f6331c9041dd1337cbcf652edf363))

## [1.25.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.24.0...v1.25.0) (2023-06-26)


### Features

* **laravel:** upgrade to Laravel 10 ([1fc65a1](https://github.com/jonathanMelly/pm2etml-intranet/commit/1fc65a1655dfab45042d9880f319c1988f60f27d))

## [1.24.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.23.5...v1.24.0) (2023-06-26)


### Features

* **eval:** export vers excel ([9217503](https://github.com/jonathanMelly/pm2etml-intranet/commit/92175032082220a39f4a02f7094811616fdafb61)), closes [#38](https://github.com/jonathanMelly/pm2etml-intranet/issues/38)

## [1.23.5](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.23.4...v1.23.5) (2023-06-07)


### Bug Fixes

* **users:** notify group change as a change ;-) ([8fcc379](https://github.com/jonathanMelly/pm2etml-intranet/commit/8fcc37946a5b73c1e47b19da5b1444d99f81fe0b))

## [1.23.4](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.23.3...v1.23.4) (2023-06-06)


### Bug Fixes

* **stat:** still missing info in some cases and enhanced UI ([c3e6b6b](https://github.com/jonathanMelly/pm2etml-intranet/commit/c3e6b6bdae5251304907742c3978e16c62d02bba))
* **stat:** triangle for failure ([c3e6b6b](https://github.com/jonathanMelly/pm2etml-intranet/commit/c3e6b6bdae5251304907742c3978e16c62d02bba))

## [1.23.3](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.23.2...v1.23.3) (2023-06-05)


### Bug Fixes

* **stat:** missing group, details on line-&gt;on column.... ([c81b45d](https://github.com/jonathanMelly/pm2etml-intranet/commit/c81b45d96f6e3d8550683cb7ccfea4207319f7d8))

## [1.23.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.23.1...v1.23.2) (2023-06-02)


### Bug Fixes

* **stat:** name length ([7d2cac8](https://github.com/jonathanMelly/pm2etml-intranet/commit/7d2cac8334105a16b09de1e1f94212f661b43ae2))

## [1.23.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.23.0...v1.23.1) (2023-06-02)


### Bug Fixes

* **stat:** bigger v space for summaries ([56abd85](https://github.com/jonathanMelly/pm2etml-intranet/commit/56abd859e120101022b1d2b7bd30632694941457))
* **stat:** point color of eval is based on success, not overall percentage until now... ([1112c71](https://github.com/jonathanMelly/pm2etml-intranet/commit/1112c71da5f94f22cecc215f7a93f44ce694c027))
* **stat:** removes dummy data ([56abd85](https://github.com/jonathanMelly/pm2etml-intranet/commit/56abd859e120101022b1d2b7bd30632694941457))

## [1.23.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.22.0...v1.23.0) (2023-06-02)


### Features

* **stat:** added summary data ([9136ecf](https://github.com/jonathanMelly/pm2etml-intranet/commit/9136ecf0a3f31d7836db4dfbdf8c3d52c42712bd)), closes [#24](https://github.com/jonathanMelly/pm2etml-intranet/issues/24)


### Bug Fixes

* **stat:** removed "legend" prefix and fixed missing label on same X multiple Y... ([9136ecf](https://github.com/jonathanMelly/pm2etml-intranet/commit/9136ecf0a3f31d7836db4dfbdf8c3d52c42712bd))

## [1.22.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.21.1...v1.22.0) (2023-05-26)


### Features

* **mutation:** keep evaluation history upon student migration (min-&gt;cin for instance...) ([6205aa1](https://github.com/jonathanMelly/pm2etml-intranet/commit/6205aa1bdcfc043abe2260ae3a420b7785b746b5))
* **sso-bridge:** added custom login callback URI ([08df9a2](https://github.com/jonathanMelly/pm2etml-intranet/commit/08df9a2d77f9fe3247b1a914b5ec738df7a052ba)), closes [#72](https://github.com/jonathanMelly/pm2etml-intranet/issues/72)
* **stat:** all data available for principal and dean ([a70e579](https://github.com/jonathanMelly/pm2etml-intranet/commit/a70e579aafc651a72c8ebc40fe076074b3e62f98))
* **stat:** show basic eval stats for student and maitre de classe ([78a3c03](https://github.com/jonathanMelly/pm2etml-intranet/commit/78a3c033244a3219a7ea2c5f8934ce35c9937a4c))


### Bug Fixes

* **filters:** apply period and timeunit filters only for app (not on guest parts) ([1e62fa8](https://github.com/jonathanMelly/pm2etml-intranet/commit/1e62fa82e5d34190f57dd915aeead04aee7af554))
* **period:** only show teacher’s groups of current academic period (filter to be added) ([78a3c03](https://github.com/jonathanMelly/pm2etml-intranet/commit/78a3c033244a3219a7ea2c5f8934ce35c9937a4c))
* **stat:** compute contracts for which project has been retired ([6b31716](https://github.com/jonathanMelly/pm2etml-intranet/commit/6b3171631a776a79b0f1725a348e5e9ffd050006))

## [1.21.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.21.0...v1.21.1) (2023-04-05)


### Bug Fixes

* **sentry:** final probe name ([46e1cc4](https://github.com/jonathanMelly/pm2etml-intranet/commit/46e1cc4813e63748503fd2a5cd0b59e3fb385f5d))

## [1.21.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.20.2...v1.21.0) (2023-04-04)


### Features

* **sentry:** add cron check ([50e095c](https://github.com/jonathanMelly/pm2etml-intranet/commit/50e095cfd22209d1804b21f32f87021e9d2d680d))

## [1.20.2](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.20.1...v1.20.2) (2023-03-29)


### Bug Fixes

* **password confirm:** deactivate password confirm as not compatible with 2nd factor auth and not available with openid... ([1d84ada](https://github.com/jonathanMelly/pm2etml-intranet/commit/1d84ada63856d5594f879486068e94b751a431fc))
* **tests:** password confirm tests parts removed ([7ee152c](https://github.com/jonathanMelly/pm2etml-intranet/commit/7ee152c6b374fe745525e2bcd9e27254113548ba))

## [1.20.1](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.20.0...v1.20.1) (2023-03-10)


### Bug Fixes

* **logout:** no sso logout for standard logout (issue on staging) ([a5bd68f](https://github.com/jonathanMelly/pm2etml-intranet/commit/a5bd68f15c4ca3ed8b9bd3c7cc4100a5b4610146))

## [1.20.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.19.0...v1.20.0) (2023-02-24)


### Features

* **contract:** ability to edit contract dates ([de57b8b](https://github.com/jonathanMelly/pm2etml-intranet/commit/de57b8b61476d64823d55c800f009860e2e98e61)), closes [#29](https://github.com/jonathanMelly/pm2etml-intranet/issues/29)
* **monitoring:** add sentry basic conf ([d4d111e](https://github.com/jonathanMelly/pm2etml-intranet/commit/d4d111ed3c524a19612782653a964f7ddd741287))


### Bug Fixes

* **sentry:** only report warnings and upper ([6aa47e0](https://github.com/jonathanMelly/pm2etml-intranet/commit/6aa47e0e325c4b387583ee75306b885584fbc88f))

## [1.19.0](https://github.com/jonathanMelly/pm2etml-intranet/compare/v1.18.1...v1.19.0) (2023-02-20)


### Features

* **laravel...:** update to laravale 9.59 (and also other deps) ([691ff0d](https://github.com/jonathanMelly/pm2etml-intranet/commit/691ff0da6a32e3f017bc529f4f3d39700a944f99))

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
