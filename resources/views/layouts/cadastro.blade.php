@php 
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 

new class extends Livewire\Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function cadastrar(): void
    {
        $dados = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $dados ['name'],
            'email' => $dados ['email'],
            'password' => Hash::make($dados['password']),
         ]);

        auth()->login($user);

        $this->redirect('/', navigate: true);
    }
}

@endphp

<div class="max-w-md mx-auto mt-16 p-8 bg-white rounded-xl shadow">
    <h1 class=""text-2xl font-bold mb-6 text-gray-800""> Criar Conta</h1>

    <form wire:submit="cadastrar" class="space-y-4"> 
        <div>
            <label class="block text-sm font-medium text-gray-700">Nome</label>
            <input type="text" wire:model="name" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orange-500 focus:border-orange-500">
            
        </div>

    
    </form>
</div>