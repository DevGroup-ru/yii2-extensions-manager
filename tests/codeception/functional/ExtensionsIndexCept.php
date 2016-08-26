<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/extensions-manager/extensions/index');
$I->seeResponseCodeIs(200);
$I->canSeeElement('[data-action="ext-info"]');
// rbac
$I->amOnPage('/extensions-manager/extensions/index?guest');
$I->cantSeeElement('[data-action="ext-info"]');
