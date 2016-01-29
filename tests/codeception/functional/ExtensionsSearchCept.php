<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/ru/extensions-manager/extensions/search');
$I->seeResponseCodeIs(200);
$I->canSeeElement('[name="query"]');
$I->fillField(['name' => 'query'], 'devgroup/');
$I->selectOption('select[name=type]', 'Расширение Yii2');
$I->click('form button[type="submit"]');
$I->canSeeElement('[data-action="ext-info"]');