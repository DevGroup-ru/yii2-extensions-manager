<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/extensions-manager/extensions/config');
$I->seeResponseCodeIs(200);
$I->canSeeElement('.box.config__renderSectionForm');
// rbac
$I->amOnPage('/extensions-manager/extensions/config?guest');
$I->cantSeeElement('.box.config__renderSectionForm');
