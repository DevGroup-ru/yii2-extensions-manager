<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/extensions-manager/extensions/search');
$I->seeResponseCodeIs(200);
$I->canSeeElement('[name="query"]');
$I->fillField(['name' => 'query'], 'devgroup/');
$I->selectOption('select[name=type]', 'dotplant-extension');
$I->click('form button[type="submit"]');
$I->canSeeElement('[data-action="ext-info"]');
// rbac
$I->amOnPage('/extensions-manager/extensions/search?guest');
$I->cantSeeElement('[name="query"]');
