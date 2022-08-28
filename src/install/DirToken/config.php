<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.28.00

//Installation date: ##DATE##

use ProtocolLive\TelegramBotLibrary\TgObjects\TgInvoiceCurrencies;
use ProtocolLive\TelegramBotLibrary\TblObjects\TblLog;
//use ProtocolLive\PhpLiveDb\Drivers;

//Token given by @BotFather
//Type: string
const Token = '##TOKEN##';

//Token created for authenticate the webhook
//Type: string
//Default: null
const TokenWebhook = ##TOKENWEBHOOK##;

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

//Use the log constants of the class TelegramBot or/and the constants defined to bot
//Tips: & and, | or, ^ xor
//Type: TblLog
//Default: TblLog::All
const Log = TblLog::Webhook | TblLog::Send | TblLog::Response;

//Default currency
//Type: TgInvoiceCurrencies
//Default: TgInvoiceCurrencies::USD
const DefaultCurrency = TgInvoiceCurrencies::USD;