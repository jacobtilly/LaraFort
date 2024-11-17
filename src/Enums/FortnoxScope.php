<?php

namespace JacobTilly\LaraFort\Enums;

enum FortnoxScope: string
{
    case ARCHIVE = 'archive';
    case ARTICLE = 'article';
    case BOOKKEEPING = 'bookkeeping';
    case COMPANYINFORMATION = 'companyinformation';
    case CONNECTFILE = 'connectfile';
    case COSTCENTER = 'costcenter';
    case CURRENCY = 'currency';
    case CUSTOMER = 'customer';
    case INBOX = 'inbox';
    case INVOICE = 'invoice';
    case NOXFINANSINVOICE = 'noxfinansinvoice';
    case OFFER = 'offer';
    case ORDER = 'order';
    case PAYMENT = 'payment';
    case PRICE = 'price';
    case PRINT = 'print';
    case PROFILE = 'profile';
    case PROJECT = 'project';
    case SALARY = 'salary';
    case SETTINGS = 'settings';
    case SUPPLIER = 'supplier';
    case SUPPLIERINVOICE = 'supplierinvoice';
    case TIMEREPORTING = 'timereporting';

    public static function getDefaultScopes(): array
    {
        return [
            self::PROFILE->value => true, // Always required
            self::ARCHIVE->value => false,
            self::ARTICLE->value => false,
            self::BOOKKEEPING->value => false,
            self::COMPANYINFORMATION->value => false,
            self::CONNECTFILE->value => false,
            self::COSTCENTER->value => false,
            self::CURRENCY->value => false,
            self::CUSTOMER->value => false,
            self::INBOX->value => false,
            self::INVOICE->value => false,
            self::NOXFINANSINVOICE->value => false,
            self::OFFER->value => false,
            self::ORDER->value => false,
            self::PAYMENT->value => false,
            self::PRICE->value => false,
            self::PRINT->value => false,
            self::PROJECT->value => false,
            self::SALARY->value => false,
            self::SETTINGS->value => false,
            self::SUPPLIER->value => false,
            self::SUPPLIERINVOICE->value => false,
            self::TIMEREPORTING->value => false,
        ];
    }

    public static function getDescription(string $scope): string
    {
        return match ($scope) {
            self::ARCHIVE->value => 'Access to archives',
            self::ARTICLE->value => 'Access to articles and article connections',
            self::BOOKKEEPING->value => 'Access to accounts, vouchers, and financial years',
            self::COMPANYINFORMATION->value => 'Access to company information',
            self::CONNECTFILE->value => 'Access to file connections',
            self::COSTCENTER->value => 'Access to cost centers',
            self::CURRENCY->value => 'Access to currencies',
            self::CUSTOMER->value => 'Access to customers',
            self::INBOX->value => 'Access to inbox',
            self::INVOICE->value => 'Access to invoices, contracts, and tax reductions',
            self::NOXFINANSINVOICE->value => 'Access to Nox Finans invoices',
            self::OFFER->value => 'Access to offers',
            self::ORDER->value => 'Access to orders',
            self::PAYMENT->value => 'Access to invoice and supplier invoice payments',
            self::PRICE->value => 'Access to prices and price lists',
            self::PRINT->value => 'Access to print templates',
            self::PROFILE->value => 'Access to profile (required)',
            self::PROJECT->value => 'Access to projects',
            self::SALARY->value => 'Access to salary-related functions',
            self::SETTINGS->value => 'Access to various settings',
            self::SUPPLIER->value => 'Access to suppliers',
            self::SUPPLIERINVOICE->value => 'Access to supplier invoices',
            self::TIMEREPORTING->value => 'Access to time reporting',
            default => 'Unknown scope',
        };
    }
}
