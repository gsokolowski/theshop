Dashboard
<!-- Today's Orders -->
<div class="card">
    Create a logout button
    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
</div>

    <div class="card-header">
        <h3 class="card-title">Today's Orders</h3>
    </div>
    <div class="card-body">
        <p>Today's Orders</p>
    </div>
</div>
