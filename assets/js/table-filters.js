// Universal Table Filter and Sort Utility
// Usage: TableFilter.init(tableId, options)

class TableFilter {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.options = {
            searchPlaceholder: '🔍 Search...',
            enableSearch: true,
            enableSort: true,
            searchColumns: [], // empty means search all columns
            sortOptions: [
                { value: 'default', text: '📋 Default Order' }
            ],
            ...options
        };
        
        if (this.table) {
            this.init();
        }
    }
    
    init() {
        this.createFilterControls();
        this.bindEvents();
    }
    
    createFilterControls() {
        const thead = this.table.querySelector('thead');
        if (!thead) return;
        
        // Create filter row
        const filterRow = document.createElement('tr');
        filterRow.className = 'table-filter-row';
        filterRow.style.backgroundColor = '#f8fafc';
        
        const headerCells = thead.querySelectorAll('th');
        const colspan = headerCells.length;
        
        const filterCell = document.createElement('th');
        filterCell.colSpan = colspan;
        filterCell.style.padding = '12px';
        filterCell.style.borderBottom = '2px solid #e2e8f0';
        
        const filterContainer = document.createElement('div');
        filterContainer.style.display = 'flex';
        filterContainer.style.gap = '12px';
        filterContainer.style.alignItems = 'center';
        filterContainer.style.flexWrap = 'wrap';
        
        // Search input
        if (this.options.enableSearch) {
            this.searchInput = document.createElement('input');
            this.searchInput.type = 'text';
            this.searchInput.placeholder = this.options.searchPlaceholder;
            this.searchInput.style.cssText = 'padding: 8px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; flex: 1; min-width: 200px;';
            filterContainer.appendChild(this.searchInput);
        }
        
        // Sort select
        if (this.options.enableSort && this.options.sortOptions.length > 1) {
            this.sortSelect = document.createElement('select');
            this.sortSelect.style.cssText = 'padding: 8px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;';
            
            this.options.sortOptions.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                this.sortSelect.appendChild(optionElement);
            });
            
            filterContainer.appendChild(this.sortSelect);
        }
        
        // Clear button
        const clearButton = document.createElement('button');
        clearButton.textContent = '🗑️ Clear';
        clearButton.style.cssText = 'padding: 8px 12px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;';
        clearButton.onclick = () => this.clearFilters();
        filterContainer.appendChild(clearButton);
        
        filterCell.appendChild(filterContainer);
        filterRow.appendChild(filterCell);
        thead.appendChild(filterRow);
    }
    
    bindEvents() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => this.filterTable());
        }
        
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', () => this.filterTable());
        }
    }
    
    filterTable() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;
        
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const searchValue = this.searchInput ? this.searchInput.value.toLowerCase() : '';
        
        // Filter rows
        const visibleRows = rows.filter(row => {
            if (!searchValue) return true;
            
            const searchColumns = this.options.searchColumns.length > 0 
                ? this.options.searchColumns 
                : Array.from(row.cells).map((_, index) => index);
            
            return searchColumns.some(columnIndex => {
                const cell = row.cells[columnIndex];
                return cell && cell.textContent.toLowerCase().includes(searchValue);
            });
        });
        
        // Hide/show rows
        rows.forEach(row => {
            row.style.display = visibleRows.includes(row) ? '' : 'none';
        });
        
        // Sort visible rows
        if (this.sortSelect && this.sortSelect.value !== 'default') {
            this.sortRows(visibleRows);
        }
        
        // Update visible count
        this.updateCount(visibleRows.length, rows.length);
    }
    
    sortRows(rows) {
        const sortValue = this.sortSelect.value;
        const [column, direction] = sortValue.split('-');
        const columnIndex = parseInt(column);
        
        if (isNaN(columnIndex)) return;
        
        rows.sort((a, b) => {
            const aText = a.cells[columnIndex] ? a.cells[columnIndex].textContent.trim() : '';
            const bText = b.cells[columnIndex] ? b.cells[columnIndex].textContent.trim() : '';
            
            // Try to parse as numbers first
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            let result;
            if (!isNaN(aNum) && !isNaN(bNum)) {
                result = aNum - bNum;
            } else {
                result = aText.localeCompare(bText);
            }
            
            return direction === 'desc' ? -result : result;
        });
        
        // Re-append sorted rows
        const tbody = this.table.querySelector('tbody');
        rows.forEach(row => tbody.appendChild(row));
    }
    
    clearFilters() {
        if (this.searchInput) this.searchInput.value = '';
        if (this.sortSelect) this.sortSelect.value = 'default';
        this.filterTable();
    }
    
    updateCount(visible, total) {
        // Find or create count display
        let countDisplay = this.table.parentNode.querySelector('.table-count');
        if (!countDisplay) {
            countDisplay = document.createElement('div');
            countDisplay.className = 'table-count';
            countDisplay.style.cssText = 'text-align: right; color: #6b7280; font-size: 12px; margin-top: 8px;';
            this.table.parentNode.appendChild(countDisplay);
        }
        
        countDisplay.textContent = visible === total 
            ? `Showing ${total} entries`
            : `Showing ${visible} of ${total} entries`;
    }
    
    // Static method for easy initialization
    static init(tableId, options = {}) {
        return new TableFilter(tableId, options);
    }
}

// Auto-initialize tables with data-filter attribute
document.addEventListener('DOMContentLoaded', function() {
    const autoTables = document.querySelectorAll('table[data-filter]');
    autoTables.forEach(table => {
        const options = table.dataset.filterOptions ? JSON.parse(table.dataset.filterOptions) : {};
        new TableFilter(table.id, options);
    });
});
