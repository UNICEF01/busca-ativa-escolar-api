<?php

namespace BuscaAtivaEscolar\Console\Commands;

use BuscaAtivaEscolar\ImportJob;
use Illuminate\Console\Command;

class ForceImportEducacensoFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:force_import_educacenso_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants_id = [
            '08655050-5c9a-11e9-8cc8-370e8daa8c0e',
            '295f3b90-ef64-11e9-a5a9-41ec46f66c0e',
            '907a24b0-9757-11e8-a4c5-175a2c7cc1d3',
            '38ed80c0-6ba8-11eb-b300-3710ba51fab8',
            'cd70cac0-74d2-11e8-9235-8d5fd2d2b57d',
            'b1e6a3a0-44c3-11e9-bbd2-7fc786533c20',
            '1a592ea0-8807-11e7-b93c-e96a77c3d8ba',
            '0c80a030-1733-11e8-b5fa-d34c06599ed0',
            '13cf60e0-41ae-11e8-b91a-99004a697b63',
            'f7e2dd60-cb0a-11e8-be97-85d12d33b484',
            '574f7f10-a6e3-11e8-9e32-dfcc3f4e5b80',
            '2dbb7400-556a-11e9-8845-a9e2fdbf27ce',
            '2f961620-5167-11e9-aa43-d5ccc01c4dac',
            '49531960-fc6f-11e7-888a-9d7d5944e2cd',
            'c32272d0-91af-11e7-968f-29e851c948be',
            '51ab75d0-4b50-11e9-9ae2-ff5346c91564',
            '64921060-a237-11e8-b539-ef1bcb62774b',
            'bad085d0-60ad-11eb-a7df-f55a10aa694e',
            '013022d0-76ba-11e7-9c2d-89049782c868',
            '27ffa340-8865-11e9-8259-f16b18ce6cdd',
            'df4a4870-8b12-11eb-b102-69ddcd8a8fe6',
            'ce883c00-0d52-11eb-9f81-2d0788157207',
            '089362a0-fe25-11e8-9da7-57aa0241c677',
            'b00f60a0-4cb3-11e9-84d1-f7d135598073',
            '9db74dc0-4bfd-11e9-a2df-91af6f999290',
            '23640610-0108-11e8-b287-d9b4fe75080e',
            '457e79e0-77c3-11e7-90ee-6ba69d1b8b9d',
            '3d0c76c0-c654-11e8-896c-09cd4e43a4f4',
            '731d2ab0-568b-11eb-a7e3-679ecc42aa13',
            '428830a0-21d4-11ea-9fe6-93efc65c1278',
            'cd33b720-8ce1-11e7-963c-6dabcf5985b8',
            'a2a47910-5694-11eb-b32c-6d77b3ac20ab',
            'd7933250-0f77-11ea-aa3e-951527a266a1',
            '5e0a4680-8e53-11e7-9144-fb2c3bd37b39',
            '36d10f80-bcca-11e8-a56f-b9ffe3234a54',
            'ad54a3a0-a1e6-11eb-84df-e9c1c9369bbd',
            '7e38e540-1bcc-11e8-95e0-ef2bedff5682',
            '19e0cb90-7c5f-11e7-9bd0-0da63c0ace20',
            '92727c40-47f1-11e8-a4b1-5d821ede1bd7',
            '20b12fc0-3c2b-11e8-ad9c-532c54923a3f',
            '78355e00-3ce1-11e8-ace2-4d985fce6305',
            'e4314b80-3db6-11e8-8cd8-e3fd30b34100',
            'e3423d10-4fb1-11e8-bbc4-b72db61ee8e5',
            '926ed1d0-239c-11e8-a21a-79e29ec4da2b',
            '5408fa00-4ef3-11e8-999b-6f5bb6ddd0f6',
            '8b2f77f0-53de-11e8-a565-59305d03d695',
            '7c1aa210-9cd4-11e8-a378-156cc729e749',
            'da70dd80-0cc7-11e8-9675-0358f9e229df',
            '879776a0-3bf9-11e8-bc2d-ff00b6063a30',
            '214c08c0-6222-11ea-b6bf-9d90804ee860',
            '691a33e0-6649-11eb-9592-897aaf9335bf',
            '349d2020-9dfb-11eb-a5f6-afc49ff7ab7c',
            'ea4f9fa0-5fa3-11ea-a746-61666769c1fb',
            '969d5f30-6aa1-11e9-bb70-7d42324c4210',
            '6d9b5e80-a416-11ea-94ba-87a2cdc6f556',
            '6edfd970-8c1f-11e8-93b6-1164573678ad',
            '7446ebf0-b281-11eb-8b97-495b9f4f9021',
            '3ff57bd0-52e7-11e9-909d-b136a6000a8f',
            'dcbe40a0-553d-11e9-8c26-4f22d0eec0db',
            '860ed3d0-a7e0-11e8-b53e-d337724c31b4',
            'ee26b570-779d-11eb-ab57-1777d2e2a078',
            '1991eee0-31cc-11e8-983f-a3e1fa2498e7',
            'b97c6a80-33ad-11e9-bb39-f795037134a4',
            '7f743ad0-6d40-11e7-b6ba-fd5c3a9d8a01',
            '6b078ee0-da98-11e7-956d-07a841a1ea6f',
            '3eb53600-c0d2-11e8-a823-9f87d2a26a75',
            '4229f740-787f-11e8-aeb7-2d04d34afebf',
            'ec6bcd00-a524-11e7-a38c-d1b636841a60',
            '9be34ce0-7b13-11e8-92e4-77d04cdd1560',
            'a84d8920-5c56-11e9-90b1-578973549e1b',
            '9514ce10-6005-11eb-a8ba-1511e157b015',
            '028e7280-2411-11e9-a6c0-b71fcfb5210b',
            '17d5e8c0-6a77-11e8-b7ae-917f023c2436',
            '4d67b030-4fc5-11e9-a235-d1cbd86658fa',
            '7a4235a0-0a12-11e9-87fe-67aed63aaf3a',
            '44cd0c60-70a8-11e8-ae17-0f9d61c0accb',
            'a1fbecf0-6f4b-11e8-9d87-41ff9d0abc4b',
            '434cec80-1d58-11e8-bbb4-63ada1bf5329',
            '5ec2cd90-71fa-11eb-aa99-75a9216f5c12',
            '7a60aca0-8da4-11eb-b93b-fd6b81cab19f',
            'da0a78e0-6fc9-11e8-b16f-13daa03a8edd',
            '190e2430-1d6d-11e9-bdc9-8746505d0f95',
            'c9ab5300-eb3f-11e7-ab31-b9facbfab652',
            '3f603ca0-6d92-11e8-a5ae-8d303f41a232',
            'e250ddd0-d295-11eb-8a06-c57003c305cd',
            '70442f70-cf9a-11eb-83b3-9744ea3c3cd5',
            '90bef720-d432-11eb-9387-b5950e51df7a',
            'b05fd9c0-d5b3-11eb-8000-25f07cbc3e63',
            '88897540-6ff0-11e8-9a54-53c57150242a',
            '83d9c6a0-5f7a-11e8-8216-356ed67f9e1c',
            '6e36b040-e4b7-11eb-b883-3d0357c0d356',
            '70160c40-9e25-11eb-8d23-07a26694a8d4',
            'd9c08280-3f45-11e9-9c62-bb36d975b60e',
            '05d6c1a0-e95c-11eb-96c6-4116a2e44ebe',
            'b9f52390-d393-11eb-9b40-410b967075be',
            '5b812e70-3906-11e9-a31d-89995af08377',
            '052e4290-a6a1-11eb-b0bd-cfd94d17d18f',
            '64913c40-8376-11e7-8b0a-09987a37f145',
            '4f5afeb0-3c6d-11e9-976e-c1993453405e',
            '246ba7d0-70a0-11e8-9083-dbed58a64a47',
            '57a66210-40df-11e9-a2a5-ef3bbfdfcf13',
            'd8784bd0-4110-11e9-bce4-c57ca2a7e1c9',
            '6001af60-66ee-11eb-802f-6b28fa50eedc',
            '795967f0-294d-11e9-aa23-195befa8f045',
            '9c1195e0-6d82-11e8-8721-c31cb040b765',
            '3119b9c0-6d9f-11e8-a7be-37a5fad20e9c',
            '48bd9100-5335-11e9-bc8c-1f013b8878d5',
            '4366aea0-88bc-11eb-81ac-2debed80794f',
            '5689aaa0-8c44-11e8-91b9-87d778e9fec6',
            '6d799e00-6501-11e9-a234-6bd9c9b95d28',
            '0e742070-e49a-11eb-9cef-e3c46555ac55',
            '6742f000-6a64-11e8-912a-f55e240dddda',
            '8df1c110-566b-11eb-a7db-334fca71d146',
            'ce3fdf60-3888-11eb-8d01-61f028b1423f',
            '9af13ab0-7d1f-11e7-a4df-5fdd768df593',
            '0bd95060-e9f4-11e9-b3c6-2987a361a389',
            'c12da1c0-3645-11e9-8fc3-b9b8310a3498',
            'e3cf96b0-296e-11e9-b07b-89e2ee878316',
            'ac508490-a607-11e8-908d-0dcb4cb68f9a',
            '7f20e0a0-ee66-11e8-aece-f3e3c39443d1',
            'bf535300-73ac-11e7-a378-1dd134deb401',
            'c9094b00-fc7c-11e7-bf28-6b5fdc87507e',
            'e60802d0-dd83-11eb-84cb-0f6d2d66baf3',
            'd1f7cbf0-fc7d-11e7-8805-23a7ab35b670',
            'c08e32c0-fc7b-11e7-b924-df800c60929d',
            'c953b6e0-ab8c-11e8-bf6a-7f5fad384a95',
            'ac9c9620-cca2-11e8-8b72-63d6f0c240b1',
            'd9832ab0-1f19-11e9-80af-d10d2d2f0e1d',
            '15041670-86a3-11e7-bcc2-51742fc150a0',
            '38cfba90-6d8d-11e8-b023-f59fbae9d09a',
            'e7949420-b5cc-11e8-a3b8-f9337a7fcd45',
            '54e46fd0-8f71-11e8-8193-adbbe1349122',
            'd0ba3e60-81d7-11e7-a30d-d35bab82a664',
            '79117a60-4e18-11e8-96da-9d13d1f2a3f8',
            'ecdecbb0-9feb-11e8-81cf-4154db353ef1',
            '9971d840-b124-11e8-90a7-e91d217142b7',
            '7a680c60-7303-11e8-ba3b-873763478d37',
            '87403b00-83a5-11e8-9e20-f1e32429e3f9',
            '6a1243e0-a705-11e8-9314-a9fea55bca1a',
            '5f754230-f74b-11e8-8b9f-05a4ec55d201',
            '24cfa450-b5d2-11e8-883f-b1713ceb8ac4',
            '95dc4440-026e-11eb-b94e-9bdf101b7b47',
            '6f8bf780-3b53-11e9-962e-59657f5a0f03',
            '9f42e210-552a-11e8-a112-df9058edf64f',
            '9719e7a0-55b6-11e9-8157-f795340ffa6d',
            'f24929c0-6fce-11e8-a2f5-730f880b9cb0',
            '775b9e00-6f09-11e8-a349-ef5889e4c541',
            '08bae460-6a55-11e8-8d66-996187fbfc54',
            'd6c6a630-4501-11e9-a205-d10fc9646a28',
            '5f1664a0-82f4-11e9-8ad2-9d71b619d383',
            'afd2fcb0-5f7e-11e8-9535-b70f18669069',
            '666c7050-c71a-11e8-a37f-67403a0dda28',
            'c4bca0a0-6a82-11e8-942e-6944576564bc',
            '3529a090-76d8-11eb-a443-07d1c9454b52',
            '40e2d4f0-8c2c-11e7-871b-cfabb07bafb9',
            '44aba140-92e8-11eb-9ced-9b1605f89468',
            'b5b125a0-5c59-11e9-a2f0-551f581477e7',
            '260b46e0-bfec-11e8-bd18-41de0159c3f8',
            '4bf74de0-7dee-11e8-b783-3fe6f535a0fa',
            '0f474030-1b80-11e9-9b4c-a93b22875251',
            'f86bfd90-02d1-11e9-ba08-773cacddbada',
            '1651d9c0-2e5e-11e9-bcc7-bd581749dc32',
            'bcf01bc0-68ae-11e8-89e2-45476b5bb191',
            '10b81370-4fd2-11e9-8de5-a9a98ce39240',
            'f8e9e900-82a5-11e7-a801-1b31de492878',
            'e6760320-6706-11eb-907b-6d7062978c6b',
            '0590a7e0-56a2-11eb-9de0-cd80c58d47f1',
            'c1213980-9610-11eb-8681-8554293e6601',
            '3bfef4a0-777a-11eb-8ce7-1182df8297d5',
            '0d4379d0-5f1d-11eb-a76e-338aa21ed377',
            'f7334010-af11-11e9-8901-07e8920592ee'
        ];

        foreach ($tenants_id as $id) {
            $job = ImportJob::where([
                ['tenant_id', '=', $id],
                ['type', '=', 'inep_educacenso_xls_chunck'],
                ['errors', 'like', '%No alive%']
            ])->get()->first();

            if ($job){
                $this->comment($job->tenant_id);
                try {
                    $job->handle();
                }catch (\Exception $e){
                    $this->comment($e->getMessage());
                }
            }
        }
    }
}
