<?php

namespace JacobTilly\LaraFort\Commands;

use Illuminate\Console\Command;
use JacobTilly\LaraFort\Facades\LaraFort;

class TestConnectionCommand extends Command
{
    protected $signature = 'larafort:testconnection';
    protected $description = 'Test the Fortnox connection by fetching company information';

    public function handle()
    {
        $this->info('Testing Fortnox connection...');

        try {
            $response = LaraFort::get('companyinformation');

            if (isset($response['CompanyInformation'])) {
                $this->info('✓ Connection successful!');
                $this->info('Company Name: ' . $response['CompanyInformation']['CompanyName']);
                $this->info('Organization Number: ' . $response['CompanyInformation']['OrganizationNumber']);

                return 0;
            }

            $this->error('Unexpected response format:');
            $this->error(json_encode($response, JSON_PRETTY_PRINT));
            return 1;

        } catch (\Exception $e) {
            $this->error('Connection test failed:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
