<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/ru/extensions-manager/extensions/config');
$I->seeResponseCodeIs(200);
$I->canSeeElement('.box.config__renderSectionForm');