<?php 

class Database{
    private $pdo;
    public function __construct(string $path)
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->pdo = new PDO("sqlite:" . $path);

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


        $this->initialize();
    }

    private function initialize()
    {
        $result = $this->pdo->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name='page'"
        );

        if (!$result->fetch()) {
            $sql = file_get_contents(__DIR__ . "/../../sql/schema.sql");
            $this->pdo->exec($sql);
        }
    }

    public function Execute($sql)
    {
        $this->pdo->exec($sql);
    }

    public function Fetch($sql)
    {
        $result = $this->pdo->query($sql);
        return $result->fetch(PDO::FETCH_ASSOC);
    }
    public function Create($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "insert into $table ($columns) values ($placeholders)";
        $statement = $this->pdo->prepare($sql);

        $statement->execute($data);
        return $this->pdo->lastInsertId();
    }
    public function Read($table, $id) 
    {
        $sql = "select * from $table where id = :id";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function Update($table, $id, $data) 
    {
        $set = [];
        foreach ($data as $key => $value) 
        {
            $set[] = "$key = :$key";
        }

        $setString = implode(", ", $set);

        $sql = "update $table set $setString where id = :id";
        $stmt = $this->pdo->prepare($sql);

        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function Delete($table, $id) 
    {
        $sql = "delete from $table where id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function Count($table)
    {
        $result = $this->Fetch("SELECT COUNT(*) as count FROM $table");
        return $result["count"];
    }

}

