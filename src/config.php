<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.26.00

//Installation date: ##DATE##

//Type: string
//Default: UTC
const Timezone = '##TIMEZONE##';

//Token given by @BotFather
//Type: string
const Token = '##TOKEN##';

//Use the test server
//Type: bool
//Default: false
const TestServer = ##TESTSERVER##;

//Default language
//Type: string
//Default: en
const DefaultLanguage = '##LANGUAGE##';

//Main admin
//Type: int
const Admin = ##ADMIN##;

//Use the debug constants of the class TelegramBot or/and the constants defined to bot
//Tips: & and, | or, ^ xor
//Type: TblDebug
//Default: TblDebug::All
const Debug = TblDebug::All ^ TblDebug::Curl;

//Default currency
//Type: TgInvoiceCurrencies 
//Default: TgInvoiceCurrencies::USD
const DefaultCurrency = TgInvoiceCurrencies::USD;