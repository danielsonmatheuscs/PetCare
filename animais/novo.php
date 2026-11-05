```php
<?php
require_once __DIR__.'/../config/db.php';

$tutores=$pdo->query("
    SELECT id,nome
    FROM tutores
    ORDER BY nome
")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $s=$pdo->prepare("
        INSERT INTO animais(
            tutor_id,
            nome,
            especie,
            raca,
            cor,
            porte,
            sexo,
            observacoes
        )
        VALUES(?,?,?,?,?,?,?,?)
    ");

    $s->execute([
        $_POST['tutor_id'],
        $_POST['nome'],
        $_POST['especie'],
        $_POST['raca'],
        $_POST['cor'],
        $_POST['porte'],
        $_POST['sexo'],
        $_POST['observacoes']
    ]);

    header('Location:index.php');
    exit;
}

$pageTitle='Novo animal';

require __DIR__.'/../includes/header.php';
?>

<header class="topbar">
    <div>
        <h1>Novo animal</h1>
        <p>Cadastre os dados do pet.</p>
    </div>
</header>

<div class="card form-card">
    <form method="post">

        <div class="form-grid">

            <div class="field">
                <label>Nome *</label>
                <input
                    name="nome"
                    required
                >
            </div>

            <div class="field">
                <label>Tutor *</label>

                <div style="position:relative;">

                    <input
                        type="text"
                        id="pesquisaTutor"
                        placeholder="Digite o nome do tutor..."
                        autocomplete="off"
                        required
                    >

                    <input
                        type="hidden"
                        name="tutor_id"
                        id="tutor_id"
                        required
                    >

                    <div
                        id="resultadosTutores"
                        style="
                            display:none;
                            position:absolute;
                            top:100%;
                            left:0;
                            right:0;
                            background:#fff;
                            border:1px solid #ddd;
                            border-radius:8px;
                            max-height:220px;
                            overflow-y:auto;
                            z-index:1000;
                        "
                    >
                    </div>

                </div>
            </div>

            <div class="field">
                <label>Espécie</label>

                <input
                    name="especie"
                    value="Cão"
                >
            </div>

            <div class="field">
                <label>Raça</label>

                <input
                    name="raca"
                >
            </div>

            <div class="field">
                <label>Cor</label>

                <input
                    name="cor"
                >
            </div>

            <div class="field">
                <label>Porte</label>

                <select name="porte">
                    <option>Pequeno</option>
                    <option selected>Médio</option>
                    <option>Grande</option>
                </select>
            </div>

            <div class="field">
                <label>Sexo</label>

                <select name="sexo">
                    <option>Macho</option>
                    <option>Fêmea</option>
                </select>
            </div>

            <div class="field full">
                <label>Observações</label>

                <textarea name="observacoes"></textarea>
            </div>

        </div>

        <div class="actions">

            <button
                type="submit"
                class="btn primary"
            >
                Salvar
            </button>

            <a
                class="btn secondary"
                href="index.php"
            >
                Cancelar
            </a>

        </div>

    </form>
</div>

<script>
const pesquisaTutor=document.getElementById('pesquisaTutor');
const tutorId=document.getElementById('tutor_id');
const resultadosTutores=document.getElementById('resultadosTutores');

const tutores=<?=json_encode($tutores,JSON_UNESCAPED_UNICODE)?>;

pesquisaTutor.addEventListener('input',function(){

    const texto=this.value.toLowerCase().trim();

    tutorId.value='';

    resultadosTutores.innerHTML='';

    if(texto===''){
        resultadosTutores.style.display='none';
        return;
    }

    const resultados=tutores.filter(function(tutor){

        return tutor.nome
            .toLowerCase()
            .includes(texto);

    });

    if(resultados.length===0){

        resultadosTutores.innerHTML=`
            <div style="padding:12px;color:#777;">
                Nenhum tutor encontrado.
            </div>
        `;

        resultadosTutores.style.display='block';

        return;
    }

    resultados.forEach(function(tutor){

        const item=document.createElement('div');

        item.textContent=tutor.nome;

        item.style.padding='12px';
        item.style.cursor='pointer';
        item.style.borderBottom='1px solid #eee';

        item.addEventListener('mouseenter',function(){
            item.style.background='#f5f5f5';
        });

        item.addEventListener('mouseleave',function(){
            item.style.background='#fff';
        });

        item.addEventListener('click',function(){

            pesquisaTutor.value=tutor.nome;

            tutorId.value=tutor.id;

            resultadosTutores.style.display='none';

        });

        resultadosTutores.appendChild(item);

    });

    resultadosTutores.style.display='block';

});

document.addEventListener('click',function(event){

    if(
        !pesquisaTutor.contains(event.target) &&
        !resultadosTutores.contains(event.target)
    ){
        resultadosTutores.style.display='none';
    }

});

document.querySelector('form').addEventListener('submit',function(event){

    if(tutorId.value===''){

        event.preventDefault();

        alert('Selecione um tutor da lista antes de salvar o animal.');

        pesquisaTutor.focus();

    }

});
</script>

<?php
require __DIR__.'/../includes/footer.php';
?>
```