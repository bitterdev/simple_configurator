<?php

defined('C5_EXECUTE') or die('Access denied');

use Bitter\SimpleConfigurator\Entity\Configurator\Submission;

/** @var Submission $submission */

$subject = t("New Submission");

$bodyHTML = $submission->getAttributesTable() . $submission->getPriceTable();

$body = t("You need to enable HTML to see the content of this email.");
