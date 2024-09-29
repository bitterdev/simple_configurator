<?php /** @noinspection PhpUnused */
/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpInconsistentReturnPointsInspection */

namespace Bitter\SimpleConfigurator\Transformer;

use Concrete\Core\Site\Service;

class MoneyTransformer
{
    protected $config;

    public function __construct(
        Service $siteService
    )
    {
        $this->config = $siteService->getSite()->getConfigRepository();
    }

    public function transform(float $amount): string
    {
        $valueFormatted = number_format(
            $amount,
            (int)$this->config->get("simple_configurator.configurator.money_formatting.decimals", 2),
            (string)$this->config->get("simple_configurator.configurator.money_formatting.decimal_point", "."),
            (string)$this->config->get("simple_configurator.configurator.money_formatting.thousands_separator", ",")
        );

        $currencySymbol = (string)$this->config->get("simple_configurator.configurator.money_formatting.currency_symbol", "USD");
        $space = str_repeat(" ", (int)$this->config->get("simple_configurator.configurator.money_formatting.currency_symbol_spaces", 1));

        if ((string)$this->config->get("simple_configurator.configurator.money_formatting.currency_symbol_position", "left") === "left") {
            if ($amount < 0) {
                $valueFormatted = number_format(
                    $amount * -1,
                    (int)$this->config->get("simple_configurator.configurator.money_formatting.decimals", 2),
                    (string)$this->config->get("simple_configurator.configurator.money_formatting.decimal_point", ","),
                    (string)$this->config->get("simple_configurator.configurator.money_formatting.thousands_separator", ".")
                );

                return "-" . $currencySymbol . $space . $valueFormatted;
            } else {
                return $currencySymbol . $space . $valueFormatted;
            }
        } else {
            return $valueFormatted . $space . $currencySymbol;
        }

    }
}