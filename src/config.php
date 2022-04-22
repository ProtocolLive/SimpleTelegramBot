<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.22.00

//PHP
/**
 * Type: string
 * Default: UTC
 */
const Timezone = 'UTC';

/**
 * Token given by @BotFather
 * Type: string
 */
const Token = '';

//Use the test server
//Type: bool
//Default: false
const TestServer = false;

/**
 * Default language
 * Type: string
 * Default: en
 */
const DefaultLanguage = 'en';

/**
 * Main admin
 * Type: int
 */
const Admin = 0;

/**
 * Use the debug constants of the class TelegramBot or/and the constants defined to bot
 * Tips: & and, | or, ^ xor
 * Type: TblDebug
 * Default: TblDebug::All
 */
const Debug = TblDebug::All;

//Default currency
//Type: TgInvoiceCurrencies 
//Default: TgInvoiceCurrencies::USD
const DefaultCurrency = TgInvoiceCurrencies::USD;