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
            '79f30fc0-6538-11ea-8e6c-b71e54d22a8a',
            'e2924f00-27be-11e8-a348-6f60a46cd096',
            '4d9dc620-6d88-11e8-b29c-19499beccb6a',
            '81e11b40-ffad-11e7-8b8d-21fe5b0e154e',
            'cbb9ef40-472d-11e9-acbc-77f7cd0e456a',
            '26015480-4f0c-11e9-b392-6f0e0a761a47',
            '48e64c60-82aa-11e7-84ea-25dff815b7a5',
            '48e64c60-82aa-11e7-84ea-25dff815b7a5',
            '48e64c60-82aa-11e7-84ea-25dff815b7a5',
            'e20f4fa0-fbab-11e7-b6b2-c53ebda10576',
            'fac346d0-428c-11e8-a976-e3a54e6387ea',
            'fac346d0-428c-11e8-a976-e3a54e6387ea',
            'd7ae36c0-b8a4-11eb-809c-7d702c475850',
            '2195b000-718d-11e8-bed9-f978e4fefa5b',
            '7752b790-6b33-11e7-8e2a-1539e1468253',
            '1d92c710-6b40-11e8-8f09-95550c983a30',
            'a94b9450-e3e2-11eb-8514-33d059603707',
            'a94b9450-e3e2-11eb-8514-33d059603707',
            '3a678cc0-ff7b-11eb-8fce-2fbb5be0c05c',
            '0a782b10-7a0d-11e8-ba14-55ee70286567',
            '53000c80-cdea-11eb-a6cf-e12eba2cea21',
            '53000c80-cdea-11eb-a6cf-e12eba2cea21',
            'ed715d50-a41c-11ea-b338-055c5e628218',
            '44cdb1a0-c7e7-11e8-a6db-332179b74c1a',
            '32173d40-6f3d-11e8-ae21-c99600b81499',
            '7752b790-6b33-11e7-8e2a-1539e1468253',
            '7752b790-6b33-11e7-8e2a-1539e1468253',
            '0abd7420-6a7b-11e8-9b2c-792333e5a93e',
            'f6120a40-d5b4-11eb-b1c4-a3f768feecec',
            'f6120a40-d5b4-11eb-b1c4-a3f768feecec',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            'cce71a60-b5f6-11e8-a24a-1bd46b9a0a47',
            'cce71a60-b5f6-11e8-a24a-1bd46b9a0a47',
            'd6f53670-569c-11eb-934f-b178f2a90fe0',
            '066068e0-ae5a-11eb-a6d9-c375e261e7e4',
            '066068e0-ae5a-11eb-a6d9-c375e261e7e4',
            '44cdb1a0-c7e7-11e8-a6db-332179b74c1a',
            '7752b790-6b33-11e7-8e2a-1539e1468253',
            '416324a0-a571-11e8-addf-532c6f87104f',
            '416324a0-a571-11e8-addf-532c6f87104f',
            'a4e69040-c3ba-11eb-9b39-3b81815db72a',
            'e1c36990-350f-11e9-b054-a35f25f43a06',
            'c9c3ac70-963b-11eb-b868-3b86ffc1ab9e',
            '1b3a57b0-a904-11e7-9001-81bddaf3a757',
            '1b3a57b0-a904-11e7-9001-81bddaf3a757',
            '7752b790-6b33-11e7-8e2a-1539e1468253',
            '46b733f0-6a74-11e9-8b20-1f2a257c3868',
            '5ec7abc0-ebbc-11eb-9f96-35ba4189e841',
            'c86dfdd0-4719-11e8-9725-b16a2ec8f322',
            '9d2974d0-198c-11e9-ad5f-a9146d0936a7',
            '521f2940-2c41-11e8-ad76-4f2b74c70372',
            '9400e680-feae-11e7-89de-63895d13bef4',
            'ecbc1480-43f6-11e9-b443-55c264971967',
            'ecbc1480-43f6-11e9-b443-55c264971967',
            '7752b790-6b33-11e7-8e2a-1539e1468253',
            'd67d5220-e679-11e8-8a93-ffc72fd874dd',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            '349d2020-9dfb-11eb-a5f6-afc49ff7ab7c',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            '8ef80420-a554-11e8-b95d-cdcef47ef6ad',
            '8aaaba90-1aec-11ec-95bb-e3396fa87e95',
            '9583c4f0-d0eb-11ea-a441-df2c68f136cd',
            'a0a22dd0-c9f2-11eb-90c3-a9f10746f193',
            '3ffd8980-7d2c-11e7-9dc4-a7cf7d63ccf2',
            '3e5b65d0-9833-11e9-af76-3fca3a62fd2c',
            'b957a0a0-c439-11e9-a0e5-653c6bac89f8',
            '6bbbd9e0-586e-11e8-a34b-e79c1e458c94',
            'b97c6a80-33ad-11e9-bb39-f795037134a4',
            '3ffd8980-7d2c-11e7-9dc4-a7cf7d63ccf2',
            '7a7bbad0-dda0-11eb-9f30-ef56147b9282',
            '8aaaba90-1aec-11ec-95bb-e3396fa87e95',
            '8aaaba90-1aec-11ec-95bb-e3396fa87e95',
            '8aaaba90-1aec-11ec-95bb-e3396fa87e95',
            'e06a4e80-ced4-11eb-8f59-8986212a98ab',
            '0c93f6f0-4188-11e8-a497-9fd3f1109678',
            '3e2d3ca0-c851-11e9-80e8-718ce6586e3d',
            'c9942930-9d5d-11eb-8873-a3684f6e8558',
            '424835e0-6ca0-11eb-8d98-3184a7bdeb71',
            '3681d1b0-f334-11e8-b9b5-f3858047965f',
            '03f6cda0-a180-11e8-a6ac-5596fccb8a69',
            '5be02ea0-4a3a-11e8-852e-9d9fb1b8cb7e',
            '38ed80c0-6ba8-11eb-b300-3710ba51fab8',
            'c9942930-9d5d-11eb-8873-a3684f6e8558',
            '521f2940-2c41-11e8-ad76-4f2b74c70372',
            'b8b92ec0-1e8a-11e9-8216-03836e8719b8',
            '1f4c16f0-d9d5-11eb-86cc-f7ce17ead348',
            '257f7af0-1a53-11e9-a6fa-695e0c320b74',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'deaafc50-db73-11e8-bc81-67a04b75b5e8',
            'dd2a2ef0-351d-11e9-bb21-95ee7dff1683',
            'dd2a2ef0-351d-11e9-bb21-95ee7dff1683',
            '3fe3bbf0-7009-11e8-921b-45293db929be',
            '257f7af0-1a53-11e9-a6fa-695e0c320b74',
            'c09cf020-f55e-11eb-a04e-5f79e4be042a',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            'e171c860-fc2b-11eb-8955-dd543b933383',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            '7da3df10-6d83-11e8-92e3-9f25f1e6307d',
            'a11783a0-98fe-11e9-b1e3-e7a1af1b32e7',
            'a0183170-a64b-11e8-865f-f54e1b93bd35',
            '8aaaba90-1aec-11ec-95bb-e3396fa87e95',
            '89ba8d90-1c85-11ec-992c-b71ebfd7a60e',
            'e477e520-2885-11e8-a7f4-8f459ae1f61f',
            '574f7f10-a6e3-11e8-9e32-dfcc3f4e5b80',
            '574f7f10-a6e3-11e8-9e32-dfcc3f4e5b80',
            '47ba9a00-f073-11eb-8a41-7189aecea591',
            '5984db00-1f1a-11e9-b2b0-7769b2482b1e',
            '35c0ded0-5690-11eb-a7b6-5167464c8e37',
            '257a6a10-68e9-11e8-8faa-f5e4e3464513',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            'c53857d0-7002-11e8-899e-7380eb6ecdec',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            '9f6eb300-e89d-11eb-8ab1-1f44ec6cd578',
            '9dc7b9e0-8a6c-11eb-8ed4-f3f2eac158ec',
            '9dc7b9e0-8a6c-11eb-8ed4-f3f2eac158ec',
            '3df7d540-56e3-11e9-8234-316a6453f1c7',
            'c53857d0-7002-11e8-899e-7380eb6ecdec',
            'ba510bc0-0ccc-11ec-a4f9-a9b7fa9562d9',
            '0c93f6f0-4188-11e8-a497-9fd3f1109678',
            '0c93f6f0-4188-11e8-a497-9fd3f1109678',
            '8d2785c0-69a0-11e8-ad9c-596109a3db0b',
            '0c93f6f0-4188-11e8-a497-9fd3f1109678',
            '0c93f6f0-4188-11e8-a497-9fd3f1109678',
            '2195b000-718d-11e8-bed9-f978e4fefa5b',
            '2195b000-718d-11e8-bed9-f978e4fefa5b',
            '05881320-0a70-11ec-9928-29f151cb3475',
            '18297130-a4f4-11ea-a355-81cf9308f906',
            'cc7a1ab0-7890-11e8-8bd0-9fbedaab669f',
            '4d3eaa30-22e4-11ec-9cf9-a9b7210d1712',
            'caf920c0-f284-11e8-a026-910f0b62cd8e',
            '54dfd6b0-5fb7-11e9-886c-cfe477fb299a',
            '62b45980-e0b7-11eb-a800-81c5694fd6e5',
            '11615c80-14cb-11ec-adcf-2318e95e59e4',
            '11615c80-14cb-11ec-adcf-2318e95e59e4',
            '11615c80-14cb-11ec-adcf-2318e95e59e4',
            '11615c80-14cb-11ec-adcf-2318e95e59e4',
            '11615c80-14cb-11ec-adcf-2318e95e59e4',
            'f36fca20-c324-11e8-9485-6725385e60c1',
            '4ed000b0-ef32-11e8-9456-390f567fdf44',
            '952a03b0-d943-11e9-9033-e7b72b7b750b',
            '60762870-67d7-11eb-95cc-8b3da7116d4d',
            'd7e80bd0-8e07-11ea-8643-0126b5f6b97b',
            'efbd0ef0-a674-11ea-bf4c-7da451a66059',
            '88abbee0-8d82-11e7-9bc4-e1e434188cbf',
            '4d3eaa30-22e4-11ec-9cf9-a9b7210d1712',
            'e21a0a60-c7be-11eb-848f-c9f423c61a30',
            'e21a0a60-c7be-11eb-848f-c9f423c61a30',
            'ab304750-01b0-11ec-a1f3-d1f1dae30f14',
            'b957a0a0-c439-11e9-a0e5-653c6bac89f8',
            'a603eb70-8269-11eb-867f-79cece68ca0f',
            '8504eee0-3458-11e9-b0a6-fbcd76f4c2be',
            '3ffd8980-7d2c-11e7-9dc4-a7cf7d63ccf2',
            'bfc85de0-1052-11ea-9012-2fe5f2d72691',
            '8504eee0-3458-11e9-b0a6-fbcd76f4c2be',
            '86bfb620-7a23-11e8-ad76-ffb4a934f448',
            '8edc80e0-6c99-11eb-abc4-bbf77c888c4e',
            '9e50dcf0-c418-11e9-9632-6bad9377eae4',
            '0f86fd80-774a-11e9-91cc-77cab41ea807',
            'bebe2ac0-8507-11e8-84d9-95ffc48e6bd4',
            '4cd10ef0-eb8f-11ea-a63a-316d0e436884',
            '7ce28650-1b2a-11e8-be62-89ca927cedff',
            '1296ebd0-731d-11e8-b75a-bf56cf011793',
            'abd2e470-d814-11eb-8811-a5fbe1f71b95',
            '44392530-81f0-11e7-9553-c374531d3acd',
            '3c540e40-54cc-11e9-92f1-89b2a38315e3',
            'c53857d0-7002-11e8-899e-7380eb6ecdec',
            '5d171b60-6e5f-11e8-aa2d-cfc9f666ca39',
            '5d171b60-6e5f-11e8-aa2d-cfc9f666ca39',
            '3c540e40-54cc-11e9-92f1-89b2a38315e3',
            'd6709d60-12f1-11eb-8a74-811b08ef9293',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            '7c827fd0-69ef-11e8-bd80-377e33249b02',
            'fbfb9fd0-b167-11ea-902a-19dc5a05fa99',
            '10b54e30-6aea-11eb-a2cd-35b6a61a5f2c',
            'fbfb9fd0-b167-11ea-902a-19dc5a05fa99',
            '1d92c710-6b40-11e8-8f09-95550c983a30',
            'a4736f20-9b2c-11e7-ac17-5b096c0dcc53',
            '43cf3400-69f7-11ea-94a0-491732afafee',
            'e8657300-9739-11e9-88f9-c3148b9f0032',
            '9d2974d0-198c-11e9-ad5f-a9146d0936a7',
            '9c624a70-8cd7-11eb-b39d-39d3a2ddac62',
            '0d84bc30-5e7c-11e8-829b-ffabe438d9bf',
            '574f7f10-a6e3-11e8-9e32-dfcc3f4e5b80',
            '8edc80e0-6c99-11eb-abc4-bbf77c888c4e',
            '9dc7b9e0-8a6c-11eb-8ed4-f3f2eac158ec',
            'd6ca1e20-6f07-11e8-af05-cfe5eb575adf',
            '9dc7b9e0-8a6c-11eb-8ed4-f3f2eac158ec',
            '4ed000b0-ef32-11e8-9456-390f567fdf44',
            '22931530-b9a1-11eb-9bc0-f3f06ca0202e',
            '68c3d330-c319-11e8-ba73-2bdf02548e3a',
            '10b54e30-6aea-11eb-a2cd-35b6a61a5f2c',
            '9dc7b9e0-8a6c-11eb-8ed4-f3f2eac158ec',
            '9dc7b9e0-8a6c-11eb-8ed4-f3f2eac158ec',
            'f3854f10-7959-11e8-a3f5-51d5321f4a7e',
            '3a3df4c0-d2bb-11eb-a17d-211406573a60',
            '8edc80e0-6c99-11eb-abc4-bbf77c888c4e',
            '8edc80e0-6c99-11eb-abc4-bbf77c888c4e',
            '03d37a60-b2db-11e8-960d-0bdc9313074f',
            '8edc80e0-6c99-11eb-abc4-bbf77c888c4e',
            'e719c260-71be-11e8-9f6b-31bf9c4cc185',
            '521f2940-2c41-11e8-ad76-4f2b74c70372',
            '322ee470-6692-11eb-9a0a-4bd4d89b2ce2',
            '322ee470-6692-11eb-9a0a-4bd4d89b2ce2',
            'b75fde80-523c-11e9-9eb7-2550106c9e77',
            '60be4b80-ea37-11eb-88e9-b5cd100e96a2',
            '60be4b80-ea37-11eb-88e9-b5cd100e96a2',
            '0706c630-56ea-11e9-ba6a-67c67b1aab4e',
            '6e36b040-e4b7-11eb-b883-3d0357c0d356'
        ];

        foreach ($tenants_id as $id) {
            $job = ImportJob::where([
                ['tenant_id', '=', $id],
                ['status', '=', 'pending']
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
