<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.30.00

//Installation date: ##DATE##

//Token given by @BotFather
//Type: string
const Token = '##TOKEN##';

//Main admin
//Type: int
const Admin = ##ADMIN##;

//Default language
//Type: string
//Default: en
const DefaultLanguage = '##LANGUAGE##';

//Use the test server
//Type: bool
//Default: false
const TestServer = ##TESTSERVER##;

//Type: string
//Default: UTC
const Timezone = '##TIMEZONE##';

//Use the debug constants of the class TelegramBot or/and the constants defined to bot
//Tips: & and, | or, ^ xor
//Type: TblDebug
//Default: TblDebug::All
const Debug = TblDebug::All ^ TblDebug::Curl ^ TblDebug::Webhook;

//Default currency
//Type: TgInvoiceCurrencies
//Default: TgInvoiceCurrencies::USD
const DefaultCurrency = TgInvoiceCurrencies::USD;