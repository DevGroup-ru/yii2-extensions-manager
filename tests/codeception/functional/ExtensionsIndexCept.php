<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/ru/extensions-manager/extensions/index');
$I->seeResponseCodeIs(200);
$I->canSeeElement('[data-action="ext-info"]');