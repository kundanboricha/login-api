<!DOCTYPE html>
<html>
<head>
    <title>Data Table</title>
</head>
<body>
    <h1>Data Table</h1>
    @if (!empty($imagePaths))
        <table>
            <thead>
                <tr>
                    <th>Image Path</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($imagePaths as $imagePath)
                    <tr>
                        <td>{{ $imagePath }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No data to display.</p>
    @endif
</body>
</html>
