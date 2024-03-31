<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.arnoldporter.com';
$spider_name = 'arnoldporter';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

$values = array();

$json = base64_encode('{
    "IsDefaultSort": true,
    "SortOption": "0",
    "SortOptionLabel": "Name",
    "AllSortOptions": [
        "Name",
        "Office",
        "Title",
        "Practice"
    ],
    "SortGroups": null,
    "ReloadFilters": false,
    "KeywordPlaceholder": "Search By Name",
    "FilterSection": {
        "Label": "Filters",
        "FilterGroups": {
            "letters": {
                "Label": "Letters",
                "QueryKey": "letters",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            },
            "practices": {
                "Label": "Practices",
                "QueryKey": "practices",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            },
            "industries": {
                "Label": "Industries",
                "QueryKey": "industries",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            },
            "initiatives": {
                "Label": "Pro Bono",
                "QueryKey": "initiatives",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": false,
                "ShouldRenderChildFilters": false,
                "HasFilters": false
            },
            "offices": {
                "Label": "Offices",
                "QueryKey": "offices",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            },
            "titles": {
                "Label": "Titles",
                "QueryKey": "titles",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            },
            "schools": {
                "Label": "Schools",
                "QueryKey": "schools",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            },
            "admissions": {
                "Label": "Admissions",
                "QueryKey": "admissions",
                "Filters": [],
                "HasActiveFilters": true,
                "AllowMultiSelect": true,
                "IsDateFilterGroup": false,
                "IsVisible": true,
                "ShouldRenderChildFilters": false,
                "HasFilters": true
            }
        },
        "SingleFilters": {
            "science degrees": {
                "Name": "Science Degrees",
                "ID": "00000000-0000-0000-0000-000000000000",
                "Url": null,
                "IsSelected": false,
                "IsEnabled": true,
                "IsVisible": true,
                "IsExpanded": false,
                "SortOrder": 0,
                "ChildFilters": []
            },
            "regions": {
                "Name": "Regions",
                "ID": "00000000-0000-0000-0000-000000000000",
                "Url": null,
                "IsSelected": false,
                "IsEnabled": true,
                "IsVisible": true,
                "IsExpanded": false,
                "SortOrder": 0,
                "ChildFilters": []
            }
        },
        "MaxFilterGroupSize": 15,
        "DateFilterGroup": null,
        "QuiTamFilters": null
    },
    "CustomStartDate": null,
    "CustomEndDate": null,
    "Language": "en",
    "Skip": 0,
    "Take": 2000,
    "InitialPageSize": 2000
}');

$data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.$json.'&url=https://www.arnoldporter.com/api/people/search'), 1);

foreach($data['GridData'] as $value)
{
	$values[] = $value;
    $row = $value;
    
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array(
        $spider_name,
        $base_url.$row['Url'],
        json_encode($row),
        'pending',
        time(),
        NULL
    ));

}

echo count($values);

?><br/>