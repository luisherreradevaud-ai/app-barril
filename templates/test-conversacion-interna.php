<?php
/**
 * Template de Prueba - Conversaciones Internas
 * Acceder via: ./?s=test-conversacion-interna
 */

// Datos de prueba - puedes cambiar estos valores
$conversation_view_name = "test";
$conversation_entity_id = "test-123";
?>

<style>
  .debug-panel {
    background: #1e1e1e;
    color: #00ff00;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    max-height: 300px;
    overflow-y: auto;
  }

  .debug-panel h5 {
    color: #00ff00;
    border-bottom: 1px solid #00ff00;
    padding-bottom: 10px;
    margin-bottom: 10px;
  }

  .debug-log {
    margin: 5px 0;
  }

  .debug-log.error {
    color: #ff0000;
  }

  .debug-log.success {
    color: #00ff00;
  }

  .debug-log.info {
    color: #00bfff;
  }

  .debug-log.warning {
    color: #ffa500;
  }

  .test-info {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .btn-debug {
    margin: 5px;
  }
</style>

<!-- Header -->
<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0 text-gray-800">
          <i class="fas fa-fw fa-comments"></i>
          <b>Test - Conversaciones Internas</b>
        </h1>
      </div>
    </div>
  </div>

  <!-- Info del Test -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="test-info">
        <h4>📋 Información del Test</h4>
        <p class="mb-2">
          <strong>Usuario:</strong> <?php echo $GLOBALS['usuario']->nombre; ?> (ID: <?php echo $GLOBALS['usuario']->id; ?>)
        </p>
        <p class="mb-2">
          <strong>View Name:</strong> <code><?php echo $conversation_view_name; ?></code>
        </p>
        <p class="mb-0">
          <strong>Entity ID:</strong> <code><?php echo $conversation_entity_id; ?></code>
        </p>
        <hr>
        <p class="mb-0 small text-muted">
          💡 <strong>Tip:</strong> Puedes cambiar estos valores editando las líneas 7-8 de este template para probar con entidades reales.
        </p>
      </div>
    </div>
  </div>

  <!-- Panel de Debug -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="debug-panel">
        <h5>📊 Debug Console</h5>
        <div id="debug-console">
          <div class="debug-log info">[INIT] Sistema de debug iniciado</div>
          <div class="debug-log info">[INFO] Usuario: <?php echo $GLOBALS['usuario']->nombre; ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botones de Debug -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="test-info">
        <h5>🎮 Controles de Debug</h5>
        <button class="btn btn-primary btn-sm btn-debug" onclick="testLoadConversation()">
          🔄 Recargar Conversación
        </button>
        <button class="btn btn-info btn-sm btn-debug" onclick="testGetData()">
          📊 Ver Datos Actuales
        </button>
        <button class="btn btn-warning btn-sm btn-debug" onclick="testClearConsole()">
          🗑️ Limpiar Console
        </button>
        <button class="btn btn-secondary btn-sm btn-debug" onclick="testInspectInstance()">
          🔍 Inspeccionar Instancia
        </button>
        <button class="btn btn-success btn-sm btn-debug" onclick="testAddDummyComment()">
          ✍️ Agregar Comentario de Prueba
        </button>
        <button class="btn btn-danger btn-sm btn-debug" onclick="testDebugEndpoint()">
          🐛 Debug Endpoint
        </button>
      </div>
    </div>
  </div>

  <!-- Ejemplo de uso del método render() -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">✨ Nuevo: Método <code>render()</code></h5>
        </div>
        <div class="card-body">
          <p class="mb-3">
            Ahora puedes renderizar el componente de conversación dinámicamente con una sola línea de código.
            Perfecto para modales, tabs, y componentes dinámicos.
          </p>

          <div class="mb-3">
            <strong>Código:</strong>
            <pre class="bg-dark text-light p-3 rounded"><code>ConversacionInterna.render('dynamic-conv', 'tarea', '456', {
  compact: true,
  height: '300px',
  placeholder: 'Ejemplo dinámico...'
});</code></pre>
          </div>

          <button class="btn btn-success btn-sm mb-3" id="btn-render-example">
            🚀 Renderizar Ejemplo Dinámico
          </button>
          <button class="btn btn-danger btn-sm mb-3" id="btn-destroy-example" style="display:none;">
            🗑️ Destruir Ejemplo
          </button>

          <!-- Contenedor para el ejemplo dinámico -->
          <div id="dynamic-conv-container" class="border rounded p-3" style="display:none;">
            <div id="dynamic-conv"></div>
          </div>

          <div class="mt-3">
            <a href="/CONVERSACION_RENDER_EXAMPLES.md" target="_blank" class="btn btn-outline-primary btn-sm">
              📖 Ver Guía Completa de Uso
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Componente de Conversación Tradicional -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">📝 Componente Tradicional (Include PHP)</h5>
        </div>
        <div class="card-body">
          <?php include($GLOBALS['base_dir']."/templates/components/conversacion-interna.php"); ?>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

<script>
// Variables globales
window.currentUserId = '<?php echo $GLOBALS['usuario']->id; ?>';
window.testConvId = '<?php echo isset($conv_unique_id) ? $conv_unique_id : "conv_" . md5($conversation_view_name . "_" . $conversation_entity_id); ?>';

/**
 * ConversacionInterna - Sistema de conversaciones internas
 */
var ConversacionInterna = (function() {
  'use strict';

  var instances = {};

  var config = {
    maxFiles: 5,
    maxFileSize: 20 * 1024 * 1024,
    refreshInterval: 60000,
    baseUrl: '/ajax'
  };

  function Conversation(containerId) {
    this.containerId = containerId;
    this.$container = $('#' + containerId);
    this.viewName = this.$container.data('view-name');
    this.entityId = this.$container.data('entity-id');
    this.conversationData = null;
    this.usuarios = [];
    this.refreshTimer = null;
    this.init();
  }

  Conversation.prototype.init = function() {
    this.loadUsuarios();
    this.loadConversation();
    this.setupEventListeners();
    this.setupAutoRefresh();
    if(typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  };

  Conversation.prototype.loadUsuarios = function() {
    var self = this;
    $.ajax({
      url: config.baseUrl + '/ajax_getSearch.php',
      method: 'GET',
      data: { type: 'usuarios', limit: 1000 },
      success: function(response) {
        if(response && Array.isArray(response)) {
          self.usuarios = response;
        }
      },
      error: function(error) {
        console.error('Error cargando usuarios:', error);
      }
    });
  };

  Conversation.prototype.loadConversation = function(preserveScroll) {
    var self = this;
    console.log('[loadConversation] Iniciando carga... preserveScroll:', preserveScroll);

    // Solo mostrar loading en la primera carga (cuando conversationData es null)
    var isFirstLoad = (self.conversationData === null);

    if(isFirstLoad) {
      self.showLoading();
    }

    $.ajax({
      url: config.baseUrl + '/ajax_getConversacion.php',
      method: 'GET',
      data: {
        nombre_vista: self.viewName,
        id_entidad: self.entityId
      },
      dataType: 'json',
      success: function(response) {
        console.log('[loadConversation] Respuesta recibida:', response);
        if(response.status === 'OK' && response.data) {
          self.conversationData = response.data;
          console.log('[loadConversation] Comentarios:', response.data.comentarios ? response.data.comentarios.length : 0);
          self.renderConversation(preserveScroll);
        } else {
          console.error('[loadConversation] Error en respuesta:', response);
          self.showError(response.mensaje || 'Error al cargar la conversación');
        }
      },
      error: function(xhr, status, error) {
        console.error('[loadConversation] Error AJAX:', error);
        console.error('[loadConversation] XHR:', xhr);
        console.error('[loadConversation] Response text:', xhr.responseText);
        self.showError('Error de conexión al cargar la conversación');
      },
      complete: function() {
        // Solo ocultar loading si se mostró
        if(isFirstLoad) {
          self.hideLoading();
        }
      }
    });
  };

  Conversation.prototype.renderConversation = function(preserveScroll) {
    var self = this;
    var $comentariosContainer = self.$container.find('.conversacion-comentarios');
    var comentarios = self.conversationData.comentarios || [];

    console.log('[renderConversation] Renderizando', comentarios.length, 'comentarios');

    var scrollPosition = 0;
    if(preserveScroll) {
      scrollPosition = $(window).scrollTop();
      console.log('[renderConversation] Guardando scroll position:', scrollPosition);
    }

    $comentariosContainer.empty();

    if(comentarios.length === 0) {
      console.log('[renderConversation] No hay comentarios, mostrando mensaje');
      self.$container.find('.conversacion-sin-comentarios').show();
    } else {
      self.$container.find('.conversacion-sin-comentarios').hide();
      comentarios.forEach(function(comentario, index) {
        console.log('[renderConversation] Renderizando comentario', index + 1, ':', comentario.id);
        var $comentario = self.renderComentario(comentario);
        $comentariosContainer.append($comentario);
      });
    }

    if(typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

    if(preserveScroll && scrollPosition > 0) {
      console.log('[renderConversation] Restaurando scroll a:', scrollPosition);
      requestAnimationFrame(function() {
        window.scrollTo(0, scrollPosition);
      });
    }
  };

  Conversation.prototype.renderComentario = function(comentario) {
    var self = this;

    // Clonar correctamente desde el template HTML5
    var templateElement = document.getElementById('conversacion-comentario-template');
    if(!templateElement) {
      console.error('[renderComentario] Template no encontrado: conversacion-comentario-template');
      return $('<div>Error: Template no encontrado</div>');
    }

    var clonedContent = templateElement.content.cloneNode(true);

    // Envolver en un div temporal para poder manipular con jQuery
    var $wrapper = $('<div>').append(clonedContent);
    var $template = $wrapper.find('.conversacion-comentario').first();

    if($template.length === 0) {
      console.error('[renderComentario] No se encontró .conversacion-comentario en el template');
      console.log('[renderComentario] Contenido del wrapper:', $wrapper.html());
      return $('<div>Error: Template mal formado</div>');
    }

    console.log('[renderComentario] Template clonado OK para comentario:', comentario.id);

    $template.attr('data-comentario-id', comentario.id);

    // Eliminar cualquier imagen que pueda existir
    $template.find('img').remove();

    $template.find('.conversacion-comentario-autor').text(comentario.nombre_autor || 'Usuario');
    $template.find('.conversacion-comentario-fecha').text(self.formatFecha(comentario.fecha_creacion));

    var contenidoHTML = self.procesarMenciones(comentario.contenido);
    $template.find('.conversacion-comentario-contenido').html(contenidoHTML);

    if(comentario.archivos && comentario.archivos.length > 0) {
      var $archivosContainer = $template.find('.conversacion-comentario-archivos');
      $archivosContainer.empty();
      comentario.archivos.forEach(function(archivo) {
        var $archivo = self.renderArchivo(archivo);
        $archivosContainer.append($archivo);
      });
    } else {
      $template.find('.conversacion-comentario-archivos').hide();
    }

    if(comentario.tags && comentario.tags.length > 0) {
      var $tagsContainer = $template.find('.conversacion-comentario-tags');
      $tagsContainer.empty();
      comentario.tags.forEach(function(tag) {
        var $badge = $('<span class="badge bg-info me-1">@' + tag.nombre_usuario + '</span>');
        $tagsContainer.append($badge);
      });
    } else {
      $template.find('.conversacion-comentario-tags').hide();
    }

    var likes = comentario.likes_info || [];
    var likesCount = likes.length;
    var currentUserId = window.currentUserId || '';
    var hasLiked = likes.some(function(like) { return like.id === currentUserId; });

    $template.find('.conversacion-likes-count').text(likesCount);

    if(hasLiked) {
      $template.find('.conversacion-btn-like').addClass('liked');
    }

    if(likesCount > 0) {
      var nombresLikes = likes.slice(0, 3).map(function(like) { return like.nombre; }).join(', ');
      if(likesCount > 3) {
        nombresLikes += ' y ' + (likesCount - 3) + ' más';
      }
      $template.find('.conversacion-likes-usuarios').text(nombresLikes);
    }

    if(comentario.id_autor !== currentUserId) {
      $template.find('.conversacion-comentario-menu').closest('.dropdown').hide();
    }

    return $template;
  };

  Conversation.prototype.renderArchivo = function(archivo) {
    var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
    var fileExtension = archivo.nombre.split('.').pop().toLowerCase();
    var isImage = imageExtensions.includes(fileExtension);

    // Crear contenedor del archivo
    var $fileItem = $('<div>')
      .addClass('d-flex align-items-center gap-2 p-2 border rounded mb-2')
      .css({
        'background': 'var(--bs-light)',
        'cursor': 'pointer'
      })
      .on('click', function() {
        window.open(archivo.ruta_archivo, '_blank');
      });

    if(isImage) {
      // Mostrar thumbnail para imágenes
      var $thumbnail = $('<img>')
        .attr('src', archivo.ruta_archivo)
        .attr('alt', archivo.nombre)
        .addClass('file-thumbnail')
        .css({
          'width': '50px',
          'height': '50px',
          'object-fit': 'cover',
          'border-radius': '4px'
        });
      $fileItem.append($thumbnail);
    } else {
      // Mostrar icono para otros archivos
      $fileItem.append($('<i>').addClass('bi bi-file-earmark'));
    }

    // Nombre y tamaño del archivo
    var $fileDetails = $('<div>').addClass('flex-grow-1');

    var metadata = typeof archivo.metadata === 'string' ? JSON.parse(archivo.metadata) : archivo.metadata;
    var size = metadata && metadata.size ? this.formatFileSize(metadata.size) : '';

    $fileDetails.append(
      $('<div>').addClass('small fw-bold').text(archivo.nombre),
      size ? $('<div>').addClass('text-muted').css('font-size', '0.75rem').text(size) : null
    );

    $fileItem.append($fileDetails);

    return $fileItem;
  };

  Conversation.prototype.procesarMenciones = function(texto) {
    if(!texto) return '';
    texto = $('<div>').text(texto).html();
    var regex = /@(?:\[(.+?)\]\(user:([^)]+)\)|([^{]+?)\{([^}]+)\})/g;
    texto = texto.replace(regex, function(match, nombre1, id1, nombre2, id2) {
      var nombre = nombre1 || nombre2;
      var id = id1 || id2;
      return '<span class="conversacion-mention" data-user-id="' + id + '">@' + nombre + '</span>';
    });
    texto = texto.replace(/\n/g, '<br>');
    return texto;
  };

  Conversation.prototype.setupEventListeners = function() {
    var self = this;

    self.$container.find('.conversacion-form').on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    });

    self.$container.find('.conversacion-btn-enviar').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      self.guardarComentario();
      return false;
    });

    // Botón de adjuntar archivos
    self.$container.find('.conversacion-btn-attach').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log('[setupEventListeners] Click en botón adjuntar');
      self.$container.find('.conversacion-input-archivos').click();
      return false;
    });

    // Dropzone: Click para seleccionar archivos (si existe)
    self.$container.find('.dropzone-area').on('click', function() {
      console.log('[setupEventListeners] Click en dropzone');
      self.$container.find('.conversacion-input-archivos').click();
    });

    // Dropzone: Drag and drop
    var $dropzone = self.$container.find('.dropzone-area');

    $dropzone.on('dragover', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('dragover');
    });

    $dropzone.on('dragleave', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');
    });

    $dropzone.on('drop', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');

      var files = e.originalEvent.dataTransfer.files;
      console.log('[setupEventListeners] Archivos arrastrados:', files.length);

      // Asignar archivos al input
      self.$container.find('.conversacion-input-archivos')[0].files = files;
      self.previewArchivos(files);
    });

    self.$container.find('.conversacion-input-archivos').on('change', function() {
      console.log('[setupEventListeners] Archivos seleccionados:', this.files.length);
      self.previewArchivos(this.files);
    });

    self.$container.on('click', '.conversacion-btn-like', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $comentario = $(this).closest('.conversacion-comentario');
      var comentarioId = $comentario.data('comentario-id');
      self.toggleLike(comentarioId);
      return false;
    });

    self.$container.on('click', '.conversacion-btn-eliminar-comentario', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var $comentario = $(this).closest('.conversacion-comentario');
      var comentarioId = $comentario.data('comentario-id');
      if(confirm('¿Estás seguro de eliminar este comentario?')) {
        self.eliminarComentario(comentarioId);
      }
      return false;
    });

    self.setupMentionAutocomplete();
  };

  Conversation.prototype.setupMentionAutocomplete = function() {
    var self = this;
    var $textarea = self.$container.find('.conversacion-input-contenido');
    $textarea.on('keyup', function(e) {
      var text = $(this).val();
      var cursorPos = this.selectionStart;
      var textBeforeCursor = text.substring(0, cursorPos);
      var atMatch = textBeforeCursor.match(/@(\w*)$/);
      if(atMatch) {
        var query = atMatch[1].toLowerCase();
        var suggestions = self.usuarios.filter(function(user) {
          return user.nombre && user.nombre.toLowerCase().indexOf(query) !== -1;
        }).slice(0, 5);
      }
    });
  };

  Conversation.prototype.guardarComentario = function() {
    var self = this;
    var $form = self.$container.find('.conversacion-form');
    var contenido = self.$container.find('.conversacion-input-contenido').val().trim();
    var archivos = self.$container.find('.conversacion-input-archivos')[0].files;

    if(!contenido) {
      alert('Por favor escribe un comentario');
      return;
    }

    if(archivos.length > config.maxFiles) {
      alert('Máximo ' + config.maxFiles + ' archivos permitidos');
      return;
    }

    for(var i = 0; i < archivos.length; i++) {
      if(archivos[i].size > config.maxFileSize) {
        alert('El archivo "' + archivos[i].name + '" excede el tamaño máximo de 20MB');
        return;
      }
    }

    var tags = self.extractMentions(contenido);

    var formData = new FormData();
    formData.append('id_conversacion', self.conversationData.conversacion.id);
    formData.append('contenido', contenido);
    formData.append('estado', 'activo');
    formData.append('tags', JSON.stringify(tags));

    for(var i = 0; i < archivos.length; i++) {
      formData.append('archivo_' + i, archivos[i]);
      formData.append('archivo_' + i + '_meta', JSON.stringify({
        name: archivos[i].name,
        description: '',
        status: 'activo'
      }));
    }

    var $btnEnviar = self.$container.find('.conversacion-btn-enviar');
    $btnEnviar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Enviando...');

    $.ajax({
      url: config.baseUrl + '/ajax_guardarComentarioConArchivos.php',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(response) {
        console.log('[guardarComentario] Respuesta del servidor:', response);
        if(response.status === 'OK') {
          console.log('[guardarComentario] Comentario guardado exitosamente, ID:', response.comentario_id);
          $form[0].reset();
          self.$container.find('.conversacion-archivos-preview').empty();
          console.log('[guardarComentario] Recargando conversación con preserveScroll=true');
          self.loadConversation(true);
        } else {
          console.error('[guardarComentario] Error en respuesta:', response);
          alert('Error al guardar: ' + (response.mensaje || 'Error desconocido'));
        }
      },
      error: function(xhr, status, error) {
        console.error('[guardarComentario] Error AJAX:', error);
        console.error('[guardarComentario] Response text:', xhr.responseText);
        alert('Error de conexión al guardar el comentario');
      },
      complete: function() {
        $btnEnviar.prop('disabled', false).text('Publicar');
      }
    });
  };

  Conversation.prototype.extractMentions = function(texto) {
    var mentions = [];
    var regex = /@(?:\[.+?\]\(user:([^)]+)\)|[^{]+?\{([^}]+)\})/g;
    var match;
    while((match = regex.exec(texto)) !== null) {
      var userId = match[1] || match[2];
      if(userId && mentions.indexOf(userId) === -1) {
        mentions.push(userId);
      }
    }
    return mentions;
  };

  Conversation.prototype.previewArchivos = function(files) {
    var self = this;
    var $preview = self.$container.find('.conversacion-archivos-preview');
    var $filesList = self.$container.find('#files-list');
    var $filesCount = self.$container.find('#files-count');

    console.log('[previewArchivos] Mostrando preview de', files.length, 'archivos');

    $filesList.empty();

    if(files.length === 0) {
      $preview.hide();
      return;
    }

    $preview.show();
    $filesCount.text(files.length);

    var imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    for(var i = 0; i < files.length; i++) {
      var file = files[i];
      var fileExtension = file.name.split('.').pop().toLowerCase();
      var isImage = imageExtensions.includes(fileExtension);

      var $fileItem = $('<div>')
        .addClass('file-item d-flex align-items-center justify-content-between');

      var $fileInfo = $('<div>').addClass('d-flex align-items-center gap-2');

      if(isImage) {
        // Crear thumbnail para imágenes
        var reader = new FileReader();
        reader.onload = (function(img) {
          return function(e) {
            img.attr('src', e.target.result);
          };
        })($('<img>')
          .addClass('file-thumbnail')
          .appendTo($fileInfo));
        reader.readAsDataURL(file);
      } else {
        // Icono para otros archivos
        $fileInfo.append($('<i>').addClass('bi bi-file-earmark'));
      }

      // Nombre y tamaño del archivo
      var $fileDetails = $('<div>').addClass('flex-grow-1');
      $fileDetails.append(
        $('<div>').addClass('small').text(file.name),
        $('<div>').addClass('text-muted').css('font-size', '0.75rem').text(self.formatFileSize(file.size))
      );

      $fileInfo.append($fileDetails);

      // Botón de eliminar
      var $removeBtn = $('<button>')
        .addClass('btn btn-sm text-danger')
        .attr('type', 'button')
        .attr('data-file-index', i)
        .html('<i class="bi bi-trash"></i>')
        .on('click', function() {
          var index = parseInt($(this).attr('data-file-index'));
          self.removeFileFromInput(index);
        });

      $fileItem.append($fileInfo, $removeBtn);
      $filesList.append($fileItem);
    }
  };

  Conversation.prototype.removeFileFromInput = function(index) {
    var self = this;
    var input = self.$container.find('.conversacion-input-archivos')[0];
    var dt = new DataTransfer();

    for(var i = 0; i < input.files.length; i++) {
      if(i !== index) {
        dt.items.add(input.files[i]);
      }
    }

    input.files = dt.files;
    self.previewArchivos(input.files);

    console.log('[removeFileFromInput] Archivo removido. Archivos restantes:', input.files.length);
  };

  Conversation.prototype.toggleLike = function(comentarioId) {
    var self = this;
    $.ajax({
      url: config.baseUrl + '/ajax_actualizarLikesComentario.php',
      method: 'POST',
      data: {
        id_comentario: comentarioId,
        accion: 'toggle'
      },
      dataType: 'json',
      success: function(response) {
        if(response.status === 'OK') {
          var $comentario = self.$container.find('[data-comentario-id="' + comentarioId + '"]');
          var $btnLike = $comentario.find('.conversacion-btn-like');
          var $count = $comentario.find('.conversacion-likes-count');
          $btnLike.toggleClass('liked');
          $count.text(response.total_likes || 0);
          setTimeout(function() {
            self.loadConversation(true);
          }, 500);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error al actualizar like:', error);
      }
    });
  };

  Conversation.prototype.eliminarComentario = function(comentarioId) {
    var self = this;
    $.ajax({
      url: config.baseUrl + '/ajax_eliminarComentario.php',
      method: 'POST',
      data: {
        id_comentario: comentarioId
      },
      dataType: 'json',
      success: function(response) {
        if(response.status === 'OK') {
          self.loadConversation(true);
        } else {
          alert('Error al eliminar: ' + (response.mensaje || 'Error desconocido'));
        }
      },
      error: function(xhr, status, error) {
        console.error('Error eliminando comentario:', error);
        alert('Error de conexión al eliminar el comentario');
      }
    });
  };

  Conversation.prototype.setupAutoRefresh = function() {
    var self = this;
    self.refreshTimer = setInterval(function() {
      self.loadConversation(true);
    }, config.refreshInterval);
  };

  Conversation.prototype.destroy = function() {
    if(this.refreshTimer) {
      clearInterval(this.refreshTimer);
    }
  };

  Conversation.prototype.showLoading = function() {
    this.$container.find('.conversacion-loading').show();
  };

  Conversation.prototype.hideLoading = function() {
    this.$container.find('.conversacion-loading').hide();
  };

  Conversation.prototype.showError = function(message) {
    console.error(message);
  };

  Conversation.prototype.formatFecha = function(fecha) {
    if(!fecha) return '';
    var date = new Date(fecha);
    var now = new Date();
    var diff = now - date;
    var seconds = Math.floor(diff / 1000);
    var minutes = Math.floor(seconds / 60);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);
    if(days > 7) {
      return date.toLocaleDateString('es-CL');
    } else if(days > 0) {
      return 'Hace ' + days + ' día' + (days > 1 ? 's' : '');
    } else if(hours > 0) {
      return 'Hace ' + hours + ' hora' + (hours > 1 ? 's' : '');
    } else if(minutes > 0) {
      return 'Hace ' + minutes + ' minuto' + (minutes > 1 ? 's' : '');
    } else {
      return 'Hace unos segundos';
    }
  };

  Conversation.prototype.formatFileSize = function(bytes) {
    if(bytes < 1024) return bytes + ' B';
    if(bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
  };

  Conversation.prototype.getFileIcon = function(mimetype) {
    if(!mimetype) return 'file';
    if(mimetype.indexOf('image/') === 0) return 'file-image';
    if(mimetype.indexOf('video/') === 0) return 'file-video';
    if(mimetype.indexOf('audio/') === 0) return 'file-audio';
    if(mimetype.indexOf('application/pdf') === 0) return 'file-text';
    if(mimetype.indexOf('application/zip') !== -1 || mimetype.indexOf('application/x-rar') !== -1) return 'file-archive';
    if(mimetype.indexOf('application/msword') !== -1 || mimetype.indexOf('application/vnd.openxmlformats') !== -1) return 'file-text';
    return 'file';
  };

  return {
    init: function(containerId) {
      if(!instances[containerId]) {
        instances[containerId] = new Conversation(containerId);
      }
      return instances[containerId];
    },
    getInstance: function(containerId) {
      return instances[containerId];
    },
    destroy: function(containerId) {
      if(instances[containerId]) {
        instances[containerId].destroy();
        delete instances[containerId];
      }
    },
    getAllInstances: function() {
      return instances;
    },

    /**
     * Renderiza el componente de conversación en un contenedor
     * Uso: ConversacionInterna.render('mi-contenedor', 'tarea', '123', { height: '400px' })
     */
    render: function(containerId, viewName, entityId, options) {
      var defaults = {
        height: '400px',
        placeholder: 'Escribe un comentario...',
        showFiles: true,
        compact: false,
        autoRefresh: true,
        showHeader: false,
        headerText: 'Conversación'
      };

      var settings = $.extend({}, defaults, options);
      var convId = 'conv_' + viewName + '_' + entityId;
      var $container = $('#' + containerId);

      if($container.length === 0) {
        console.error('[ConversacionInterna.render] Contenedor no encontrado:', containerId);
        return null;
      }

      // Generar HTML completo
      var html = this.generateHTML(convId, viewName, entityId, settings);

      // Insertar en el contenedor
      $container.html(html);

      // Auto-inicializar
      this.init(convId);

      console.log('[ConversacionInterna.render] Componente renderizado:', convId);

      return convId; // Retorna el ID para poder destruirlo después
    },

    /**
     * Genera el HTML del componente
     */
    generateHTML: function(convId, viewName, entityId, settings) {
      var headerHTML = settings.showHeader ?
        '<div class="conversacion-header"><h5 class="mb-0"><i data-lucide="message-square" class="me-2"></i>' + settings.headerText + '</h5></div>' : '';

      var textareaRows = settings.compact ? '1' : '2';

      var dropzoneHTML = settings.showFiles ? `
        <div class="conversacion-dropzone">
          <input type="file" class="conversacion-input-archivos d-none" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
          <div class="dropzone-area text-center p-3 border-start border-end border-bottom">
            <i class="bi bi-cloud-upload" style="font-size: 1.5rem; color: var(--bs-secondary);"></i>
            <p class="mb-0 small">Arrastra archivos aquí o haz clic para seleccionar</p>
          </div>
        </div>
      ` : '';

      var filesPreviewHTML = settings.showFiles ? `
        <div class="conversacion-archivos-preview" style="display: none;">
          <div class="mb-0 border-start border-end" style="border-color: var(--bs-border-color);">
            <div class="p-2" id="files-list"></div>
          </div>
        </div>
      ` : '';

      return `
        <div id="${convId}" class="conversacion-interna-container"
             data-view-name="${viewName}"
             data-entity-id="${entityId}">

          ${headerHTML}

          <div class="conversacion-loading text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="conversacion-postbox">
                <form class="conversacion-form">
                  <div class="input-group">
                    <textarea class="form-control conversacion-input-contenido"
                              rows="${textareaRows}"
                              placeholder="${settings.placeholder}"
                              required
                              style="overflow-y: hidden; resize: none; border-bottom-left-radius: 0;"></textarea>
                    <button type="button" class="btn btn-primary conversacion-btn-enviar" style="border-bottom-right-radius: 0;">
                      Publicar
                    </button>
                  </div>
                  ${filesPreviewHTML}
                  ${dropzoneHTML}
                </form>
              </div>

              <div class="conversacion-comentarios" style="max-height: ${settings.height}; overflow-y: auto;"></div>

              <div class="conversacion-sin-comentarios text-center py-5 text-muted" style="display:none;">
                <i data-lucide="message-circle" class="mb-2" style="width: 48px; height: 48px;"></i>
                <p>Aún no hay comentarios. Sé el primero en comentar.</p>
              </div>
            </div>
          </div>
        </div>
      `;
    }
  };
})();

/**
 * Sistema de Debug
 */
var DebugLogger = {
  log: function(message, type) {
    type = type || 'info';
    var $console = $('#debug-console');
    var timestamp = new Date().toLocaleTimeString();
    var logClass = 'debug-log ' + type;
    var icon = {
      'info': 'ℹ️',
      'success': '✅',
      'error': '❌',
      'warning': '⚠️'
    }[type] || 'ℹ️';
    var $log = $('<div class="' + logClass + '">[' + timestamp + '] ' + icon + ' ' + message + '</div>');
    $console.append($log);
    $console.parent().scrollTop($console.parent()[0].scrollHeight);
    console.log('[ConversacionDebug] ' + message);
  },
  error: function(message) {
    this.log(message, 'error');
  },
  success: function(message) {
    this.log(message, 'success');
  },
  warning: function(message) {
    this.log(message, 'warning');
  },
  clear: function() {
    $('#debug-console').empty();
    this.log('Console limpiado', 'info');
  }
};

function testLoadConversation() {
  DebugLogger.log('Ejecutando test: Recargar Conversación');
  var instance = ConversacionInterna.getInstance(window.testConvId);
  if(instance) {
    instance.loadConversation();
  } else {
    DebugLogger.error('No se encontró instancia');
  }
}

function testGetData() {
  DebugLogger.log('Ejecutando test: Ver Datos Actuales');
  var instance = ConversacionInterna.getInstance(window.testConvId);
  if(instance) {
    console.log('=== DATOS ACTUALES ===');
    console.log('Container ID:', instance.containerId);
    console.log('View Name:', instance.viewName);
    console.log('Entity ID:', instance.entityId);
    console.log('Conversation Data:', instance.conversationData);
    console.log('Usuarios cargados:', instance.usuarios.length);
    DebugLogger.success('Datos impresos en la consola del navegador (F12)');
  } else {
    DebugLogger.error('No se encontró instancia');
  }
}

function testClearConsole() {
  DebugLogger.clear();
}

function testInspectInstance() {
  DebugLogger.log('Ejecutando test: Inspeccionar Instancia');
  var instances = ConversacionInterna.getAllInstances();
  console.log('=== TODAS LAS INSTANCIAS ===');
  console.log(instances);
  DebugLogger.success('Instancias impresas en la consola (F12)');
  var instance = ConversacionInterna.getInstance(window.testConvId);
  if(instance) {
    DebugLogger.log('Instancia actual encontrada');
    DebugLogger.log('View: ' + instance.viewName + ', Entity: ' + instance.entityId);
    DebugLogger.log('Comentarios: ' + (instance.conversationData ? instance.conversationData.comentarios.length : 0));
  }
}

function testAddDummyComment() {
  DebugLogger.log('Ejecutando test: Agregar Comentario de Prueba');
  var instance = ConversacionInterna.getInstance(window.testConvId);
  if(instance) {
    var $textarea = instance.$container.find('.conversacion-input-contenido');
    var dummyText = 'Este es un comentario de prueba generado el ' + new Date().toLocaleString();
    $textarea.val(dummyText);
    DebugLogger.success('Texto de prueba agregado al textarea');
    DebugLogger.log('Haz clic en "Publicar" para enviarlo');
  } else {
    DebugLogger.error('No se encontró instancia');
  }
}

function testDebugEndpoint() {
  DebugLogger.log('Ejecutando test: Debug Endpoint');
  $.ajax({
    url: '/ajax/ajax_test_simple.php',
    method: 'GET',
    dataType: 'text',
    success: function(rawResponse) {
      console.log('=== RAW RESPONSE ===');
      console.log(rawResponse);
      try {
        var response = JSON.parse(rawResponse);
        console.log('=== PARSED RESPONSE ===');
        console.log(response);
        if(response.status === 'OK') {
          DebugLogger.success('Endpoint simple OK');
          if(response.captured_output && response.captured_output.length > 0) {
            DebugLogger.warning('Output capturado: ' + response.captured_output_length + ' bytes');
            console.log('Captured output:', response.captured_output);
          } else {
            DebugLogger.success('No hay output previo');
          }
          testDebugEndpointFull();
        } else {
          DebugLogger.error('Error: ' + response.error);
        }
      } catch(e) {
        DebugLogger.error('No se pudo parsear JSON: ' + e.message);
        console.log('Raw response:', rawResponse);
      }
    },
    error: function(xhr, status, error) {
      DebugLogger.error('Error AJAX: ' + error);
      console.error('XHR:', xhr);
      console.error('Response text:', xhr.responseText);
    }
  });
}

function testDebugEndpointFull() {
  DebugLogger.log('Ejecutando test completo...');
  $.ajax({
    url: '/ajax/ajax_debug_conversacion.php',
    method: 'GET',
    dataType: 'text',
    success: function(rawResponse) {
      console.log('=== DEBUG ENDPOINT RAW ===');
      console.log(rawResponse);
      try {
        var response = JSON.parse(rawResponse);
        console.log('=== DEBUG ENDPOINT PARSED ===');
        console.log(response);
        DebugLogger.success('Debug info impreso en consola (F12)');
        if(response.classes_exist) {
          var allExist = Object.values(response.classes_exist).every(function(v) { return v === true; });
          if(allExist) {
            DebugLogger.success('Todas las clases existen');
          } else {
            DebugLogger.error('Algunas clases no existen: ' + JSON.stringify(response.classes_exist));
          }
        }
        if(response.tables_exist) {
          DebugLogger.success('Tablas encontradas: ' + response.tables_exist.length);
        }
        if(response.conversacion_test) {
          if(response.conversacion_test.success) {
            DebugLogger.success('Test de conversación OK - ID: ' + response.conversacion_test.id);
          } else {
            DebugLogger.error('Test de conversación FALLÓ: ' + response.conversacion_test.error);
          }
        }
      } catch(e) {
        DebugLogger.error('No se pudo parsear JSON del debug endpoint: ' + e.message);
        console.log('Raw response:', rawResponse);
      }
    },
    error: function(xhr, status, error) {
      DebugLogger.error('Error en debug endpoint: ' + error);
      console.error('Debug endpoint error:', xhr.responseText);
    }
  });
}

window.onerror = function(message, source, lineno, colno, error) {
  DebugLogger.error('JavaScript Error: ' + message + ' (Línea: ' + lineno + ')');
  console.error('Full error:', error);
  return false;
};

$(document).ready(function() {
  DebugLogger.success('Document ready - jQuery inicializado');
  if(window.currentUserId) {
    DebugLogger.log('Usuario actual configurado: ' + window.currentUserId);
  }

  // Inicializar el componente de conversación tradicional
  ConversacionInterna.init(window.testConvId);
  DebugLogger.success('Conversación inicializada con ID: ' + window.testConvId);

  // Ejemplo dinámico con render()
  var dynamicConvId = null;

  $('#btn-render-example').on('click', function() {
    DebugLogger.log('Renderizando ejemplo dinámico...');

    // Renderizar conversación dinámica
    dynamicConvId = ConversacionInterna.render(
      'dynamic-conv',
      'tarea_ejemplo',
      '456',
      {
        compact: true,
        height: '300px',
        placeholder: 'Ejemplo dinámico - prueba a comentar...',
        showHeader: true,
        headerText: 'Conversación Dinámica'
      }
    );

    DebugLogger.success('Conversación dinámica renderizada: ' + dynamicConvId);

    // Mostrar contenedor y botón destruir
    $('#dynamic-conv-container').slideDown();
    $('#btn-render-example').hide();
    $('#btn-destroy-example').show();
  });

  $('#btn-destroy-example').on('click', function() {
    if(dynamicConvId) {
      DebugLogger.log('Destruyendo conversación dinámica: ' + dynamicConvId);

      ConversacionInterna.destroy(dynamicConvId);
      dynamicConvId = null;

      DebugLogger.success('Conversación dinámica destruida');

      // Ocultar contenedor y botón destruir
      $('#dynamic-conv-container').slideUp();
      $('#btn-destroy-example').hide();
      $('#btn-render-example').show();
    }
  });
});
</script>
