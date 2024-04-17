#!/bin/bash

echo "Start populate setup"

echo "Start cities"
php ../artisan maintenance:reindex_all_cities

echo "Start children"
php ../artisan maintenance:reindex_all_children

echo "Start schools"
php ../artisan maintenance:reindex_all_schools
