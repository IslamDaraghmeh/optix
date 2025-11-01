# DataTables Implementation Guide

This document describes the comprehensive DataTables implementation across all listing pages in the Optical CRM system.

## Overview

All data tables in the application now use Yajra Laravel DataTables with server-side processing, advanced sorting, filtering, and export capabilities.

## Features Implemented

### Core Features
- **Server-Side Processing**: Efficient handling of large datasets
- **Advanced Search**: Search across all columns
- **Column Sorting**: Click any column header to sort
- **Pagination**: Configurable page lengths (10, 25, 50, 100, All)
- **Responsive Design**: Mobile-friendly tables
- **Export Functionality**: Copy, CSV, Excel, PDF, and Print

### User Experience
- **Loading Indicators**: Animated spinner during data loading
- **Styled Buttons**: Gradient-styled action and export buttons
- **Internationalization**: All text supports Laravel translations
- **Custom Styling**: Tailwind CSS integration with DataTables

## Implemented Pages

### 1. Patients (`/patients`)
**Table ID**: `patientsTable`
**Columns**:
- Name
- Phone
- Email
- Birth Date
- Address
- Stats (Exams/Glasses/Sales counts)
- Created At
- Actions

**Features**:
- Relationship counts displayed
- Inline action buttons (View, Edit, Delete)
- Export excludes Actions column

---

### 2. Exams (`/exams`)
**Table ID**: `examsTable`
**Columns**:
- Patient (Name & Phone)
- Right Eye (SPH, CYL, AXIS)
- Left Eye (SPH, CYL, AXIS)
- Exam Date
- Created At
- Actions

**Features**:
- Formatted prescription data
- Patient relationship eager loading
- Date formatting

---

### 3. Glasses (`/glasses`)
**Table ID**: `glassesTable`
**Columns**:
- Patient (Name & Phone)
- Lens Type
- Frame Type
- Price (formatted with $)
- Status (color-coded badges)
- Created At
- Actions

**Features**:
- Status badges:
  - 🟡 Pending (Yellow)
  - 🔵 Ready (Blue)
  - 🟢 Delivered (Green)
- Currency formatting
- Null patient handling

---

### 4. Sales (`/sales`)
**Table ID**: `salesTable`
**Columns**:
- Patient (or "Walk-in Customer")
- Items Count
- Payment Status (color-coded)
- Amounts (Total/Paid/Remaining)
- Sale Date
- Created At
- Actions

**Features**:
- Payment status badges:
  - 🟢 Paid (Green)
  - 🟡 Partial (Yellow)
  - 🔴 Unpaid (Red)
- Dynamic amount display
- Items count from JSON

---

### 5. Expenses (`/expenses`)
**Table ID**: `expensesTable`
**Columns**:
- Title
- Category (color-coded)
- Amount (formatted)
- Payment Method
- Vendor Info
- Expense Date
- Created At
- Actions

**Features**:
- 10 color-coded category badges
- Vendor and receipt info combined
- Multiple payment methods

---

### 6. Users (`/users`)
**Table ID**: `usersTable`
**Columns**:
- Name
- Email
- Roles (color-coded badges)
- Permissions Count
- Created At
- Actions

**Features**:
- Multiple role badges
- Role color coding:
  - 🔴 Admin
  - 🟣 Manager
  - 🔵 Doctor
  - 🟢 Receptionist
  - 🟡 Technician
- Security: Cannot delete own account
- Additional permissions count

---

### 7. Stock (`/stock`)
**Table ID**: `stockTable`
**Columns**:
- Item Name & Code
- Type (color-coded)
- Status (color-coded)
- Quantity (with min quantity)
- Prices (Cost & Selling)
- Brand & Supplier
- Movements Count
- Created At
- Actions

**Features**:
- Stock status badges:
  - 🔴 Out of Stock (quantity = 0)
  - 🟡 Low Stock (quantity ≤ minimum)
  - 🟢 In Stock (quantity > minimum)
- Color-coded quantity display
- Type badges for different item types
- Stock movements count

---

## Technical Implementation

### Backend (Controllers)

Each controller follows this pattern:

```php
use Yajra\DataTables\Facades\DataTables;

public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Model::select([/* columns */])
            ->with('relationships')
            ->withCount('relationships');

        return DataTables::of($query)
            ->addColumn('action', function ($item) {
                // Generate action buttons HTML
            })
            ->editColumn('date_field', function ($item) {
                return $item->date_field->format('Y-m-d H:i');
            })
            ->addColumn('custom_column', function ($item) {
                // Generate custom content
            })
            ->rawColumns(['action', 'custom_column'])
            ->make(true);
    }

    return view('resource.index');
}
```

### Frontend (Views)

Each view includes:

1. **Table Structure**:
```html
<table id="tableName" class="min-w-full divide-y divide-gray-200 display nowrap">
    <thead>
        <tr>
            <th>Column 1</th>
            <!-- More columns -->
        </tr>
    </thead>
</table>
```

2. **CSS Libraries** (in @push('styles')):
- DataTables core CSS
- Buttons CSS
- Responsive CSS
- Custom styling

3. **JavaScript Libraries** (in @push('scripts')):
- jQuery
- DataTables core
- Buttons (with JSZip, pdfMake)
- Export buttons (HTML5, Print)
- Responsive

4. **Initialization**:
```javascript
$('#tableName').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('resource.index') }}',
    columns: [/* column definitions */],
    dom: 'Bfrtip',
    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
    responsive: true,
    pageLength: 25,
    order: [[6, 'desc']]
});
```

## Export Functionality

### Available Formats

1. **Copy**: Copies data to clipboard
2. **CSV**: Comma-separated values
3. **Excel**: .xlsx format
4. **PDF**: Landscape A4 format
5. **Print**: Browser print dialog

### Export Configuration

All exports:
- Exclude Actions column: `exportOptions: { columns: ':visible:not(:last-child)' }`
- Include visible columns only
- Respect current filters and search
- Format data appropriately

## Customization

### Styling

Custom styles are defined in each view's `@push('styles')` section:

```css
#tableName_wrapper .dt-button {
    background: linear-gradient(135deg, #17877B 0%, #14A38B 100%);
    color: white !important;
    border-radius: 0.75rem;
    padding: 0.5rem 1rem;
}
```

### Language/Translations

All text uses Laravel translations:

```javascript
language: {
    search: "_INPUT_",
    searchPlaceholder: "{{ __('Search records...') }}",
    lengthMenu: "{{ __('Show') }} _MENU_ {{ __('entries') }}",
    // More translations...
}
```

### Page Length Options

Default configuration:
```javascript
pageLength: 25,
lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
```

## Performance Optimizations

### Server-Side
1. **Selective Column Loading**: Only loads needed columns
2. **Eager Loading**: Relationships loaded efficiently
3. **Indexed Columns**: Database indexes on searchable columns
4. **Query Optimization**: Minimal database queries

### Client-Side
1. **Deferred Rendering**: Rows rendered on demand
2. **CDN Libraries**: Fast loading from CDNs
3. **Responsive Tables**: Better mobile performance
4. **Cached Queries**: Browser caching enabled

## Troubleshooting

### Common Issues

#### 1. DataTables not loading
**Solution**: Check browser console for errors. Ensure jQuery loads before DataTables.

#### 2. Export buttons not working
**Solution**: Verify JSZip and pdfMake libraries are loaded.

#### 3. No data showing
**Solution**:
- Check controller returns JSON for AJAX requests
- Verify route exists and is correct
- Check browser Network tab for 500 errors

#### 4. Styling issues
**Solution**: Ensure Tailwind CSS is loaded and custom styles are in place.

#### 5. Search not working
**Solution**: Verify columns have `name` attribute matching database columns.

## Browser Compatibility

Tested and working on:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Dependencies

### PHP Packages
- `yajra/laravel-datatables-oracle`: ^10.11.4
- `yajra/laravel-datatables-buttons`: ^9.1.4
- `yajra/laravel-datatables-html`: ^9.4.3

### JavaScript Libraries (CDN)
- jQuery 3.7.0
- DataTables 1.13.7
- DataTables Buttons 2.4.2
- JSZip 3.10.1
- pdfMake 0.2.7
- DataTables Responsive 2.5.0

## Maintenance

### Adding New Columns

1. **Controller**: Add column to DataTable query
```php
->addColumn('new_column', function ($item) {
    return $item->formatted_value;
})
->rawColumns(['action', 'new_column'])
```

2. **View**: Add column header and definition
```html
<th>New Column</th>
```
```javascript
{ data: 'new_column', name: 'new_column' }
```

### Modifying Export Formats

Edit button configuration:
```javascript
{
    extend: 'pdf',
    orientation: 'landscape', // or 'portrait'
    pageSize: 'A4', // or 'A3', 'LETTER'
    exportOptions: {
        columns: ':visible:not(:last-child)'
    }
}
```

## Security Considerations

1. **CSRF Protection**: All forms include CSRF tokens
2. **Authentication**: Routes protected by middleware
3. **Authorization**: Action buttons respect user permissions
4. **SQL Injection**: Using Eloquent ORM prevents injection
5. **XSS Protection**: Laravel's Blade escaping enabled

## Backup Files

All original views backed up with `.blade.php.backup` extension:
- `patients/index.blade.php.backup`
- `exams/index.blade.php.backup`
- `glasses/index.blade.php.backup`
- `sales/index.blade.php.backup`
- `expenses/index.blade.php.backup`
- `users/index.blade.php.backup`

To restore original view:
```bash
cp resource/index.blade.php.backup resource/index.blade.php
```

## Future Enhancements

Potential improvements:
1. Column visibility toggle
2. Advanced filtering UI
3. Bulk actions (delete, export selected)
4. Column reordering
5. Save user preferences (page length, column order)
6. Real-time updates (via WebSockets)
7. Custom export templates
8. Scheduled exports

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console
3. Review DataTables documentation: https://datatables.net/
4. Review Yajra DataTables docs: https://yajrabox.com/docs/laravel-datatables/

## Summary

All data tables are now fully functional with:
- ✅ Server-side processing
- ✅ Advanced search and filtering
- ✅ Column sorting
- ✅ Pagination
- ✅ Export to multiple formats
- ✅ Responsive design
- ✅ Custom styling
- ✅ Internationalization support
- ✅ Performance optimization
- ✅ Security features

The implementation provides a professional, efficient, and user-friendly data management experience across the entire application.
