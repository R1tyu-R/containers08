
<?php

require_once __DIR__ . '/testframework.php';

require_once dirname(__DIR__) . '/site/config.php';
require_once dirname(__DIR__) . '/site/modules/database.php';
require_once dirname(__DIR__) . '/site/modules/page.php';

$testFramework = new TestFramework();


function testDbConnection() {
    global $config;

    try {
        $db = new Database($config["db"]["path"]);
        return assertExpression($db !== null, "DB connected", "DB connection failed");
    } catch (Exception $e) {
        return assertExpression(false, "", "Exception: " . $e->getMessage());
    }
}


function testDbCount() {
    global $config;

    $db = new Database($config["db"]["path"]);
    $count = $db->Count("page");

    return assertExpression($count >= 3, "Count works", "Count failed");
}


function testDbCreate() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        "title" => "Test title",
        "content" => "Test content"
    ]);

    return assertExpression($id > 0, "Create works", "Create failed");
}

function testDbRead() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $data = $db->Read("page", 1);

    return assertExpression(
        isset($data["title"]) && isset($data["content"]),
        "Read works",
        "Read failed"
    );
}


function testDbUpdate() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        "title" => "Old title",
        "content" => "Old content"
    ]);

    $db->Update("page", $id, [
        "title" => "New title",
        "content" => "New content"
    ]);

    $updated = $db->Read("page", $id);

    return assertExpression(
        $updated["title"] === "New title",
        "Update works",
        "Update failed"
    );
}


function testDbDelete() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $id = $db->Create("page", [
        "title" => "To delete",
        "content" => "Delete me"
    ]);

    $db->Delete("page", $id);

    $deleted = $db->Read("page", $id);

    return assertExpression(
        !$deleted,
        "Delete works",
        "Delete failed"
    );
}

function testDbFetch() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $result = $db->Fetch("SELECT * FROM page WHERE id = 1");

    return assertExpression(
        isset($result["id"]),
        "Fetch works",
        "Fetch failed"
    );
}


function testDbExecute() {
    global $config;

    $db = new Database($config["db"]["path"]);

    $db->Execute("INSERT INTO page (title, content) VALUES ('Exec', 'Test')");

    return assertExpression(
    true,
    "Execute works",
    "Execute failed"
    );
}


function testPageRender() {
    $page = new Page(__DIR__ . '/../site/templates/index.tpl');

    $html = $page->Render([
        "title" => "Hello",
        "content" => "World"
    ]);

    return assertExpression(
        strpos($html, "Hello") !== false && strpos($html, "World") !== false,
        "Render works",
        "Render failed"
    );
}

$testFramework->add('Database connection', 'testDbConnection');
$testFramework->add('Count', 'testDbCount');
$testFramework->add('Create', 'testDbCreate');
$testFramework->add('Read', 'testDbRead');
$testFramework->add('Update', 'testDbUpdate');
$testFramework->add('Delete', 'testDbDelete');
$testFramework->add('Fetch', 'testDbFetch');
$testFramework->add('Execute', 'testDbExecute');
$testFramework->add('Page Render', 'testPageRender');


$testFramework->run();

echo $testFramework->getResult();

