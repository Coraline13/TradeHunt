<?php

class Session
{
    /**
     * @var int the session's ID
     */
    private $id;
    /**
     * @var int the user's ID
     */
    private $user_id;
    /**
     * @var string unique random generated token
     */
    private $token;

    /**
     * @var DateTime token expiration date
     */
    private $expiration;

    /**
     * Session constructor.
     * @param int $id
     * @param int $user_id
     * @param string $token
     * @param DateTime $expiration
     */
    private function __construct($id, $user_id, $token, $expiration)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->token = $token;
        $this->expiration = $expiration;
    }

    /**
     * Create a new session and save it into the database.
     * @param User $user associated user
     * @param DateTime $expiration session validity
     * @return Session session object
     * @throws APIException if session create fails
     */
    public static function create($user, $expiration)
    {
        global $db;
        $token = bin2hex(openssl_random_pseudo_bytes(10));
        try {
            $stmt = $db->prepare("INSERT INTO sessions(user_id, token, expiration) VALUES (:user_id, :token, :expiration)");
            $stmt->bindValue(":user_id", $user->getId());
            $stmt->bindValue(":token", $token);
            $stmt->bindValue(":expiration", $expiration);
            $stmt->execute();

            $sid = (int) $db->lastInsertId();

            log_debug(sprintf("Opened session %d for user #%s (%d)", $sid, $user->getUsername(), $user->getId()));
            return new Session($sid, $user->getId(), $token, $expiration);
        } catch (PDOException $e) {
            throw new APIException(ERROR_GENERIC_API, $e, "fail to create session");
        }
    }

    /**
     * @return int the session's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string unique random generated token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return DateTime token expiration date
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
}