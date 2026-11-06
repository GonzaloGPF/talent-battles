<?php

namespace Database\Seeders;

use Doctrine\DBAL\Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws Exception
     */
    public function run(): void
    {
        $this->truncate();

        Model::unguard();

        $this->call([
            CountrySeeder::class,
            RegionSeeder::class,
            UserSeeder::class,
            AlbumSeeder::class,
            TrackSeeder::class,
            DesignSeeder::class,
            BattleSeeder::class,
            VoteSeeder::class,
            FavoriteListSeeder::class,
            FavoriteSeeder::class,
            LikeSeeder::class,
            DownloadSeeder::class,
            CommentSeeder::class,
            ProductSeeder::class,
            CartSeeder::class,
            OrderSeeder::class, // creates orders and orderLines
            SaleSeeder::class,
            ActivitySeeder::class,
            //            TagSeeder::class,
            DonationSeeder::class,
            ReportSeeder::class,
            NotificationSeeder::class,
            EmailSeeder::class,
            InvitationSeeder::class,
            FileSeeder::class,
        ]);
    }

    /**
     * @throws Exception
     */
    private function truncate(): void
    {
        Schema::disableForeignKeyConstraints();

        $tableNames = Schema::getTableListing(schemaQualified: false);

        collect($tableNames)
            ->filter(fn (string $table) => $table !== 'migrations')
            ->each(fn (string $table) => DB::table($table)->truncate());

        Schema::enableForeignKeyConstraints();
    }
}
