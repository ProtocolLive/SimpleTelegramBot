<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.27.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

enum StbDbListeners{
  case Chat;
  case ChatMy;
  case Document;
  case Text;
  case InlineQuery;
  case Invoice;
  case InvoiceCheckout;
  case InvoiceShipping;
  case Photo;
  case Voice;
}