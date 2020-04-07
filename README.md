<h1><img src="https://user-images.githubusercontent.com/49572917/78736928-b2934180-7924-11ea-95cc-a897c8773ecf.png"/></h1>
<p>Microframework PHP 7 simples para estudo e criação de pequenos sistemas WEB.</p>

<h2>Documentação</h2>
<ul>
    <li>Em breve</li>
</ul>

<h2>O que é este projeto</h2>
<p>Este microframework PHP 7 MVC orientado a objetos está sendo usado para estudo pessoal para compreensão mais aprofundada dos fundamentos do PHP e dos padrões de projeto. A ideia é fazer um framework fácil de compreender e que dê mais liberdade para o programador fazer as coisas do seu jeito ao mesmo tempo que o direciona, como um guia.</p>

<p><b>Atenção!</b><br>Este projeto está em fase alpha de desenvolvimento. Isso significa que ele pode sofrer alterações e remoções de funcionalidades e comportamentos sem prévio aviso que comprometem sistemas ou sites que utilizem versões anteriores. Portanto, evite atualizar o framework caso o esteja utilizando em um projeto real.</p>

<h2>Principais funcionalidades</h2>
<ul>
    <li>Conexão com banco de dados facilitada;</li>
    <li>Consultas SQL com paginação;</li>
    <li>Controle de autenticação;</li>
    <li>Controle de permissões;</li>
    <li>Validação de dados;</li>
    <li>Controle de rotas de URLs;</li>
    <li>Possibilidade de criação de APIs;</li>
    <li>Envio de e-mails através do próprio microframework através da biblioteca <a href="https://github.com/PHPMailer/PHPMailer">PHPMailer</a>.</li>
</ul>

<h2>Estágio atual</h2>
<ul>
    <li>Verificação de bugs;</li>
    <li>Criação de site com a documentação de uso.</li>
</ul>

<h3>Implementações futuras</h3>
<ul>
    <li>Acesse a <a href="https://github.com/andregalastri/galastri-framework/issues/2">lista de implementações</a>.</li>
</ul>

<h2>Alterações importantes</h2>
<ul>
    <li>05/04/2020: Incorporada a biblioteca <a href="https://github.com/PHPMailer/PHPMailer">PHPMailer</a> versão 6.1.5 como classe Mailer para envio de e-mails;</li>
    <li>05/11/2019: Melhorado sistema de parâmetros para rotas. Agora é possível definir rótulos aos parâmetros, recuperar estes parâmetros na controller através destes rótulos e obrigar a URL a contê-los ou não, dependendo da configuração;</li>
    <li>25/10/2019: Aplicado padrão Singleton para a classe principal Galastri e componentes Authentication, Chain, Debug, Permission, Redirect e Route;</li>
    <li>21/10/2019: Envio inicial.</li>
</ul>
