<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\Region;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    // Gunakan ini agar menggunakan pagination milik livewire
    use WithPagination;

    public $paginate = 10, $search, $formVisible = false;

    // Untuk mengupdate 'search' yang ada di url
    protected $queryString = [
        'search' => ['except' => ''],
    ];

    // Membuat listener untuk emit yang dibuat di komponen lain
    protected $listeners = ['userAdded', 'closeForm', 'userEdited'];

    public function render()
    {
        return view('livewire.users.index', [
            'users' => $this->search
                ? User::where('name', 'like', '%' . $this->search . '%')
                ->latest()->paginate($this->paginate)
                : User::latest()->paginate($this->paginate),
            'regions' => Region::all(),
        ]);
    }

    // Untuk menampilkan form tambah
    public function create()
    {
        $this->formVisible = 'create';
    }

    // Untuk menampilkan notifikasi dari emit yang dikirim dari komponen create
    public function userAdded()
    {
        session()->flash('message', 'User added successfully');
        // Tutup form
        $this->closeForm();
    }

    // Untuk menutup form
    public function closeForm()
    {
        $this->formVisible = false;
    }

    // Untuk menghapus data
    public function destroy(User $user)
    {
        $user->delete();

        // Untuk menghapus foto siswa di penyimpanan jika foto tersebut hasil upload
        if (preg_match('/upload/', $user->photo)) {
            Storage::delete($user->photo);
        }

        // Untuk memberi notifikasi
        session()->flash('message', 'User deleted successfully');
    }

    public function detail(User $user, Region $regions)
    {
        $this->formVisible = 'detail';
        // Untuk mengirim data user yang di klik ke komponen lain (komponen edit)
        $this->emit('userDetail', $user, $regions);
    }
    // Untuk menampilkan form edit
    public function edit(User $user, Region $regions)
    {
        $this->formVisible = 'edit';
        // Untuk mengirim data user yang di klik ke komponen lain (komponen edit)
        $this->emit('userEdit', $user, $regions);
    }

    // Untuk menampilkan notifikasi dari emit yang dikirim dari komponen edit
    public function userEdited()
    {
        session()->flash('message', 'User edited successfully');
        // Tutup form
        $this->closeForm();
    }
}
