<?php

use Illuminate\Database\Seeder;
use App\Tag;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	// get all tags from tags.json file and seed to table using foreach loop
    	$json = File::get('database/data/tags.json');
    	$tags = json_decode($json);
    	foreach ($tags as $tag) {
    		Tag::insert([

    				    'id' => $tag->id,
    				    'name' => $tag->name,
    				    'description' => $tag->description,
    				    'category' => $tag->category

    				]
    			);
    	}
        
    }
}
