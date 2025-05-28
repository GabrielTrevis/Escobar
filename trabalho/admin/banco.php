<?php
/**
 * Classe Banco - Responsável pela conexão com o banco de dados MySQL
 * Implementa o padrão Singleton para garantir uma única instância de conexão
 */
class Banco
{
    // Configurações de acesso ao banco de dados
    private static $dbNome = 'lojavirtual';    // Nome do banco de dados
    private static $dbHost = 'localhost';      // Servidor do banco de dados (geralmente localhost)
    private static $dbUsuario = 'root';        // Usuário do MySQL
    private static $dbSenha = '';              // Senha do MySQL (vazia neste caso)
    
    // Variável que armazenará a conexão ativa
    private static $cont = null;
    
    /**
     * Construtor privado para evitar instanciação direta
     * Isso força o uso do método estático conectar()
     */
    public function __construct() 
    {
        die('A função Init nao é permitido!');
    }
    
    /**
     * Método para estabelecer conexão com o banco de dados
     * Retorna a conexão PDO ativa ou cria uma nova se não existir
     */
    public static function conectar()
    {
        // Verifica se já existe uma conexão ativa
        if(null == self::$cont)
        {
            try
            {
                // Tenta criar uma nova conexão PDO com MySQL
                self::$cont = new PDO("mysql:host=".self::$dbHost.";"."dbname=".self::$dbNome, self::$dbUsuario, self::$dbSenha); 
            }
            catch(PDOException $exception)
            {
                // Em caso de erro na conexão, encerra a execução e exibe a mensagem de erro
                die($exception->getMessage());
            }
        }
        // Retorna a conexão ativa
        return self::$cont;
    }
    
    /**
     * Método para encerrar a conexão com o banco de dados
     * Define a variável de conexão como null
     */
    public static function desconectar()
    {
        self::$cont = null;
    }
}
?>
