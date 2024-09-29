<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Concrete\Package\SimpleConfigurator\Controller\SinglePage\Dashboard\SimpleConfigurator;

use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Form\Service\Validation;
use Concrete\Core\Site\Service;

class Settings extends DashboardPageController
{
    /** @var Validation */
    protected $formValidator;

    public function on_start()
    {
        parent::on_start();
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->formValidator = $this->app->make(Validation::class);
    }

    public function view()
    {
        /** @var Service $siteService */
        /** @noinspection PhpUnhandledExceptionInspection */
        $siteService = $this->app->make(Service::class);
        $site = $siteService->getSite();
        $config = $site->getConfigRepository();

        if ($this->request->getMethod() === "POST") {
            $this->formValidator->setData($this->request->request->all());
            $this->formValidator->addRequiredToken("update_settings");
            $this->formValidator->addRequired("notificationMailAddress", t("You need to enter a valid notification mail address."));
            $this->formValidator->addRequired("currencySymbol", t("You need to enter a currency symbol."));
            $this->formValidator->addRequired("currencyCode", t("You need to enter a currency code."));
            $this->formValidator->addRequired("currencySymbolPosition", t("You need to enter the symbol position."));
            $this->formValidator->addRequired("currencySymbolSpaces", t("You need to enter the symbol spaces."));
            $this->formValidator->addRequired("decimals", t("You need to enter the decimals."));
            $this->formValidator->addRequired("decimalPoint", t("You need to enter a decimal point."));
            $this->formValidator->addRequired("thousandsSeparator", t("You need to enter a thousands separator."));

            if ($this->formValidator->test()) {
                $config->save("simple_configurator.configurator.notification_mail_address", (string)$this->request->request->get("notificationMailAddress"));
                $config->save("simple_configurator.configurator.money_formatting.currency_symbol", (string)$this->request->request->get("currencySymbol"));
                $config->save("simple_configurator.configurator.money_formatting.currency_code", (string)$this->request->request->get("currencyCode"));
                $config->save("simple_configurator.configurator.money_formatting.currency_symbol_position", (string)$this->request->request->get("currencySymbolPosition"));
                $config->save("simple_configurator.configurator.money_formatting.currency_symbol_spaces", (int)$this->request->request->get("currencySymbolSpaces"));
                $config->save("simple_configurator.configurator.money_formatting.decimals", (int)$this->request->request->get("decimals"));
                $config->save("simple_configurator.configurator.money_formatting.decimal_point", (string)$this->request->request->get("decimalPoint"));
                $config->save("simple_configurator.configurator.money_formatting.thousands_separator", (string)$this->request->request->get("thousandsSeparator"));

                if (!$this->error->has()) {
                    $this->set("success", t("The settings has been successfully updated."));
                }
            } else {
                /** @var ErrorList $errorList */
                $errorList = $this->formValidator->getError();

                foreach ($errorList->getList() as $error) {
                    $this->error->add($error);
                }
            }
        }

        $this->set("currencySymbolPositions", [
            "left" => t("Left"),
            "right" => t("Right")
        ]);

        $this->set("notificationMailAddress", (string)$config->get("simple_configurator.configurator.notification_mail_address"));
        $this->set("currencySymbol", (string)$config->get("simple_configurator.configurator.money_formatting.currency_symbol", "USD"));
        $this->set("currencyCode", (string)$config->get("simple_configurator.configurator.money_formatting.currency_code", "USD"));
        $this->set("currencySymbolPosition", (string)$config->get("simple_configurator.configurator.money_formatting.currency_symbol_position", "left"));
        $this->set("currencySymbolSpaces", (int)$config->get("simple_configurator.configurator.money_formatting.currency_symbol_spaces", 1));
        $this->set("decimals", (int)$config->get("simple_configurator.configurator.money_formatting.decimals", 2));
        $this->set("decimalPoint", (string)$config->get("simple_configurator.configurator.money_formatting.decimal_point", "."));
        $this->set("thousandsSeparator", (string)$config->get("simple_configurator.configurator.money_formatting.thousands_separator", ","));
    }
}