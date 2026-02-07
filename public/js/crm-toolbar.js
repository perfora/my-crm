(function (window, document) {
  'use strict';

  function init(options) {
    const cfg = Object.assign({
      container: document,
      selectAllSelector: '#select-all',
      rowCheckboxSelector: '.row-checkbox',
      duplicateBtnSelector: '#btn-duplicate',
      deleteBtnSelector: '#btn-delete',
      selectionCountSelector: '#selection-count',
      columnToggleBtnSelector: '#column-toggle-btn',
      columnMenuSelector: '#column-menu',
      columnToggleSelector: '.column-toggle',
      columnArrowSelector: '#column-arrow',
      storageKey: null,
      onColumnToggle: null,
      onSelectionChange: null
    }, options || {});

    const root = cfg.container || document;
    const selectAll = root.querySelector(cfg.selectAllSelector);
    const duplicateBtn = root.querySelector(cfg.duplicateBtnSelector);
    const deleteBtn = root.querySelector(cfg.deleteBtnSelector);
    const selectionCount = root.querySelector(cfg.selectionCountSelector);
    const columnBtn = root.querySelector(cfg.columnToggleBtnSelector);
    const columnMenu = root.querySelector(cfg.columnMenuSelector);
    const columnArrow = root.querySelector(cfg.columnArrowSelector);

    let selectedIds = [];

    function getRowCheckboxes() {
      return Array.from(root.querySelectorAll(cfg.rowCheckboxSelector));
    }

    function setColumnArrow() {
      if (!columnArrow || !columnMenu) return;
      columnArrow.textContent = columnMenu.classList.contains('hidden') ? '▼' : '▲';
    }

    function updateSelection() {
      selectedIds = getRowCheckboxes().filter(cb => cb.checked).map(cb => Number(cb.dataset.id));

      if (duplicateBtn) duplicateBtn.disabled = selectedIds.length === 0;
      if (deleteBtn) deleteBtn.disabled = selectedIds.length === 0;
      if (selectionCount) selectionCount.textContent = selectedIds.length ? (selectedIds.length + ' kayıt seçili') : '';

      if (selectAll) {
        const all = getRowCheckboxes();
        const checked = all.filter(cb => cb.checked);
        selectAll.checked = all.length > 0 && all.length === checked.length;
      }

      if (typeof cfg.onSelectionChange === 'function') {
        cfg.onSelectionChange(selectedIds.slice());
      }
    }

    function applyColumnVisibility(column, isVisible) {
      if (typeof cfg.onColumnToggle === 'function') {
        cfg.onColumnToggle(column, isVisible);
      } else {
        root.querySelectorAll('.col-' + column).forEach(el => {
          el.style.display = isVisible ? '' : 'none';
        });
      }
    }

    function saveColumnPrefs() {
      if (!cfg.storageKey) return;
      const prefs = {};
      root.querySelectorAll(cfg.columnToggleSelector).forEach(cb => {
        prefs[cb.dataset.column] = cb.checked;
      });
      localStorage.setItem(cfg.storageKey, JSON.stringify(prefs));
    }

    function loadColumnPrefs() {
      if (!cfg.storageKey) return;
      const raw = localStorage.getItem(cfg.storageKey);
      if (!raw) return;

      try {
        const prefs = JSON.parse(raw);
        root.querySelectorAll(cfg.columnToggleSelector).forEach(cb => {
          if (Object.prototype.hasOwnProperty.call(prefs, cb.dataset.column)) {
            cb.checked = !!prefs[cb.dataset.column];
          }
          applyColumnVisibility(cb.dataset.column, cb.checked);
        });
      } catch (e) {
        console.warn('Sütun tercihleri okunamadı:', e);
      }
    }

    if (columnBtn && columnMenu) {
      columnBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        columnMenu.classList.toggle('hidden');
        setColumnArrow();
      });

      document.addEventListener('click', function (e) {
        if (!columnMenu.contains(e.target) && !columnBtn.contains(e.target)) {
          columnMenu.classList.add('hidden');
          setColumnArrow();
        }
      });
    }

    root.addEventListener('change', function (e) {
      if (e.target.matches(cfg.rowCheckboxSelector)) {
        updateSelection();
      }

      if (e.target.matches(cfg.columnToggleSelector)) {
        applyColumnVisibility(e.target.dataset.column, e.target.checked);
        saveColumnPrefs();
      }
    });

    if (selectAll) {
      selectAll.addEventListener('change', function () {
        getRowCheckboxes().forEach(cb => {
          cb.checked = selectAll.checked;
        });
        updateSelection();
      });
    }

    loadColumnPrefs();
    setColumnArrow();
    updateSelection();

    return {
      getSelectedIds: function () { return selectedIds.slice(); },
      refreshSelection: updateSelection,
      applyColumnVisibility: applyColumnVisibility
    };
  }

  window.CrmToolbar = { init: init };
})(window, document);
