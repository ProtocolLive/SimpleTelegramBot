<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.08.22.00

namespace ProtocolLive\TelegramBotLibrary\StbObjects;

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