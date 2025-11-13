// Configuração da API
const API_URL = 'api/usuarios.php';

// Elementos do DOM
const formCriar = document.getElementById('formCriar');
const formAtualizar = document.getElementById('formAtualizar');
const tabelaUsuarios = document.getElementById('tabelaUsuarios');
const alertContainer = document.getElementById('alertContainer');
const modalEditar = document.getElementById('modalEditar');
const modalFechar = document.getElementById('modalFechar');
const tabBtnCriar = document.getElementById('tabBtnCriar');
const tabBtnLista = document.getElementById('tabBtnLista');
const tabCriar = document.getElementById('tabCriar');
const tabLista = document.getElementById('tabLista');

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    carregarUsuarios();
    
    if (formCriar) {
        formCriar.addEventListener('submit', criarUsuario);
    }
    
    if (formAtualizar) {
        formAtualizar.addEventListener('submit', atualizarUsuario);
    }
    
    if (modalFechar) {
        modalFechar.addEventListener('click', fecharModal);
    }
    
    if (tabBtnCriar) {
        tabBtnCriar.addEventListener('click', () => abrirAba('criar'));
    }
    
    if (tabBtnLista) {
        tabBtnLista.addEventListener('click', () => abrirAba('lista'));
    }
    
    // Fechar modal ao clicar fora
    window.addEventListener('click', (e) => {
        if (e.target === modalEditar) {
            fecharModal();
        }
    });
});

// Abrir abas
function abrirAba(aba) {
    const tabs = {
        'criar': [tabBtnCriar, tabCriar],
        'lista': [tabBtnLista, tabLista]
    };
    
    // Remove classe active de todas as abas
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Adiciona classe active na aba selecionada
    tabs[aba][0].classList.add('active');
    tabs[aba][1].classList.add('active');
    
    if (aba === 'lista') {
        carregarUsuarios();
    }
}

// Criar usuário
async function criarUsuario(e) {
    e.preventDefault();
    
    const dados = {
        nome_completo: document.getElementById('nome_completo').value,
        cpf: document.getElementById('cpf').value,
        email: document.getElementById('email').value,
        telefone: document.getElementById('telefone').value,
        senha_usuario: document.getElementById('senha_usuario').value
    };
    
    // Validações básicas
    if (!validarFormulario(dados)) {
        return;
    }
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const resultado = await response.json();
        
        if (response.ok) {
            mostrarAlerta('Usuário criado com sucesso!', 'success');
            formCriar.reset();
            
            // Aguarda 2 segundos antes de mudar de aba
            setTimeout(() => {
                abrirAba('lista');
            }, 1500);
        } else {
            mostrarAlerta(resultado.erro || 'Erro ao criar usuário', 'error');
        }
    } catch (erro) {
        mostrarAlerta('Erro ao conectar com a API: ' + erro.message, 'error');
    }
}

// Carrega todos os usuários
async function carregarUsuarios() {
    try {
        const response = await fetch(`${API_URL}?acao=listar`);
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            preencherTabela(resultado.dados);
        } else {
            mostrarAlerta(resultado.erro || 'Erro ao carregar usuários', 'error');
        }
    } catch (erro) {
        mostrarAlerta('Erro ao conectar com a API: ' + erro.message, 'error');
    }
}

// Preencher tabela com usuários
function preencherTabela(usuarios) {
    const tbody = document.getElementById('usuariosTableBody');
    
    if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #999;">Nenhum usuário cadastrado</td></tr>';
        return;
    }
    
    tbody.innerHTML = usuarios.map(usuario => {
        const dataFormatada = new Date(usuario.data_criacao).toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });

        const statusClass = usuario.ativo ? 'status-active' : 'status-inactive';
        const statusTexto = usuario.ativo ? 'Ativo' : 'Inativo';

        // Mostrar apenas os primeiros 8 caracteres do hash da senha
        const senhaExibida = usuario.senha_usuario ? usuario.senha_usuario.substring(0, 8) + '...' : '-';

        return `
            <tr>
                <td>${usuario.id}</td>
                <td>${usuario.nome_completo}</td>
                <td>${formatarCPF(usuario.cpf)}</td>
                <td>${usuario.email}</td>
                <td>${usuario.telefone || '-'}</td>
                <td>${senhaExibida}</td>
                <td><span class="status-badge ${statusClass}">${statusTexto}</span></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-edit" onclick="abrirModalEditar(${usuario.id})">Editar</button>
                        <button class="btn btn-danger" onclick="confirmarDelecao(${usuario.id})">Deletar</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Abrir modal de edição
async function abrirModalEditar(id) {
    try {
        const response = await fetch(`${API_URL}?acao=obter&id=${id}`);
        const resultado = await response.json();

        if (resultado.sucesso) {
            const usuario = resultado.dados;
            document.getElementById('usuarioId').value = usuario.id;
            document.getElementById('nomeAtualizar').value = usuario.nome_completo;
            document.getElementById('emailAtualizar').value = usuario.email;
            document.getElementById('telefoneAtualizar').value = usuario.telefone || '';
            document.getElementById('senhaAtualizar').value = ''; // Sempre vazio para segurança

            modalEditar.style.display = 'flex'; // Centralizar modal
        } else {
            mostrarAlerta(resultado.erro || 'Erro ao obter usuário', 'error');
        }
    } catch (erro) {
        mostrarAlerta('Erro ao conectar com a API: ' + erro.message, 'error');
    }
}

// Fechar modal
function fecharModal() {
    modalEditar.style.display = 'none';
    formAtualizar.reset();
}

// Atualizar usuário
async function atualizarUsuario(e) {
    e.preventDefault();

    const id = document.getElementById('usuarioId').value;
    const senha = document.getElementById('senhaAtualizar').value;
    const dados = {
        id: parseInt(id),
        nome_completo: document.getElementById('nomeAtualizar').value,
        email: document.getElementById('emailAtualizar').value,
        telefone: document.getElementById('telefoneAtualizar').value
    };

    // Adicionar senha apenas se foi preenchida
    if (senha) {
        dados.senha_usuario = senha;
    }

    if (!dados.nome_completo || !dados.email) {
        mostrarAlerta('Preencha os campos obrigatórios', 'error');
        return;
    }

    if (!validarEmail(dados.email)) {
        mostrarAlerta('Email inválido', 'error');
        return;
    }

    // Validar senha se foi preenchida
    if (senha && senha.length < 6) {
        mostrarAlerta('Nova senha deve ter pelo menos 6 caracteres', 'error');
        return;
    }

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });

        const resultado = await response.json();

        if (response.ok) {
            mostrarAlerta('Usuário atualizado com sucesso!', 'success');
            fecharModal();
            carregarUsuarios();
        } else {
            mostrarAlerta(resultado.erro || 'Erro ao atualizar usuário', 'error');
        }
    } catch (erro) {
        mostrarAlerta('Erro ao conectar com a API: ' + erro.message, 'error');
    }
}

// Confirmar e deletar usuário
function confirmarDelecao(id) {
    if (confirm('Tem certeza que deseja deletar este usuário?')) {
        deletarUsuario(id);
    }
}

// Deletar usuário
async function deletarUsuario(id) {
    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE'
        });
        
        const resultado = await response.json();
        
        if (response.ok) {
            mostrarAlerta('Usuário deletado com sucesso!', 'success');
            carregarUsuarios();
        } else {
            mostrarAlerta(resultado.erro || 'Erro ao deletar usuário', 'error');
        }
    } catch (erro) {
        mostrarAlerta('Erro ao conectar com a API: ' + erro.message, 'error');
    }
}

// Mostrar alerta
function mostrarAlerta(mensagem, tipo) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${tipo} show`;
    alert.textContent = mensagem;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    // Auto remover alerta após 5 segundos
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Validações
function validarFormulario(dados) {
    if (!dados.nome_completo) {
        mostrarAlerta('Nome completo é obrigatório', 'error');
        return false;
    }
    
    if (!dados.cpf) {
        mostrarAlerta('CPF é obrigatório', 'error');
        return false;
    }

    if (!validarCPF(dados.cpf)) {
        mostrarAlerta('CPF inválido', 'error');
        return false;
    }
    
    if (!dados.email) {
        mostrarAlerta('Email é obrigatório', 'error');
        return false;
    }
    
    if (!validarEmail(dados.email)) {
        mostrarAlerta('Email inválido', 'error');
        return false;
    }
    
    if (!dados.senha_usuario) {
        mostrarAlerta('Senha do usuário é obrigatória', 'error');
        return false;
    }
    
    if (dados.senha_usuario.length < 6) {
        mostrarAlerta('Senha do usuário deve ter pelo menos 6 caracteres', 'error');
        return false;
    }
    
    return true;
}

function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Função de validação de CPF
function validarCPF(cpf) {
    // Remove caracteres especiais
    cpf = cpf.replace(/[^\d]/g, '');

    // Verifica se tem 11 dígitos
    if (cpf.length !== 11) {
        return false;
    }

    // Verifica se não é uma sequência repetida
    if (/^(\d)\1{10}$/.test(cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf[i]) * (10 - i);
    }
    let resto = soma % 11;
    let digito1 = (resto < 2) ? 0 : 11 - resto;

    // Calcula o segundo dígito verificador
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf[i]) * (11 - i);
    }
    resto = soma % 11;
    let digito2 = (resto < 2) ? 0 : 11 - resto;

    // Verifica se os dígitos correspondem
    return (digito1 === parseInt(cpf[9]) && digito2 === parseInt(cpf[10]));
}

function formatarCPF(cpf) {
    if (!cpf) return '-';
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
}
