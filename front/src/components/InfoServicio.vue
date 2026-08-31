<template>
  <!-- Cabecera compacta: los mismos datos, en la menor cantidad de líneas posible -->
  <q-card-section v-if="header" class="q-pa-xs bg-blue-1 info-servicio">
    <div class="datos">
      <span class="dato">
        <q-icon name="badge" color="blue" size="13px" />
        <b>CÓDIGO:</b> {{ header.codigo || header.id }}
      </span>

      <span class="dato">
        <q-icon name="local_hospital" color="blue" size="13px" />
        <b>ATENCIÓN:</b> {{ (header.tipo_atencion || '') === 'SI' ? 'SUS' : 'EXT' }}
      </span>

      <span class="dato">
        <q-icon name="assignment_ind" color="blue" size="13px" />
        <b>NRO. REGISTRO:</b> {{ header.nro_registro || '-' }}
      </span>

      <span class="dato">
        <q-icon name="person" color="blue" size="13px" />
        <b>PACIENTE:</b> {{ header.paciente_nombre || '-' }}
      </span>

      <span class="dato">
        <q-icon name="cake" color="blue" size="13px" />
        <b>EDAD:</b> {{ header.paciente_edad || '-' }}
      </span>

      <span class="dato">
        <q-icon name="wc" color="blue" size="13px" />
        <b>SEXO:</b> {{ header.paciente_genero || '-' }}
      </span>

      <span class="dato">
        <q-icon name="medical_services" color="blue" size="13px" />
        <b>MÉDICO SOL.:</b> {{ header.doctor_nombre || '-' }}
      </span>

      <span class="dato">
        <q-icon name="healing" color="blue" size="13px" />
        <b>DX:</b>
        {{ header.diagnostico_select ? header.diagnostico_select : (header.diagnostico_clinico || '-') }}
      </span>

      <span class="dato">
        <q-icon name="local_hospital" color="blue" size="13px" />
        <b>EST. DE SALUD:</b> {{ header.establecimiento_salud || '-' }}
      </span>

      <span class="dato">
        <q-icon name="event" color="blue" size="13px" />
        <b>CÓD. MUESTRA:</b> {{ `${header.codigo || '-'}-${header.nro_registro || '-'}` }}
      </span>

      <span class="dato">
        <q-icon name="schedule" color="blue" size="13px" />
        <b>TIEMPO:</b>
        <q-chip color="blue" text-color="white" size="9px" dense class="q-my-none q-mx-xs">
          {{ tiempoTranscurrido }}
        </q-chip>
      </span>

      <span class="dato">
        <b>FECHA PRE ANALÍTICA:</b> {{ header.fecha_envio_analitica || '-' }}
      </span>
    </div>
  </q-card-section>

  <q-card-section class="q-pa-xs servicios">
    <span class="titulo">SERVICIOS SOLICITADOS ({{ (header?.servicios || []).length }}):</span>
    <q-chip
      v-for="servicio in header?.servicios"
      :key="servicio.id"
      class="q-mr-xs q-my-none"
      color="blue"
      text-color="blue"
      size="9px"
      dense
      outline
    >
      {{ servicio.nombre }} ({{ servicio.area?.name }})
    </q-chip>
  </q-card-section>
</template>
<script>
import moment from 'moment';
export default {
  name: 'InfoServicio',
  props: {
    header: {
      type: Object,
      required: false,
      default: null,
    },
    fecha_fin: {
      type: String,
      required: false,
      default: null,
    },
  },
  computed: {
    tiempoTranscurrido() {
      if (!this.header || !this.header.fecha_solicitud) {
        return '-';
      }
      const fechaSolicitud = moment(this.header.fecha_creacion);
      const ahora = moment(this.fecha_fin || new Date());
      const duracion = moment.duration(ahora.diff(fechaSolicitud));

      const dias = duracion.days();
      const horas = duracion.hours();
      const minutos = duracion.minutes();

      let resultado = '';
      if (dias > 0) {
        resultado += `${dias}d `;
      }
      if (horas > 0) {
        resultado += `${horas}h `;
      }
      if (minutos > 0) {
        resultado += `${minutos}m`;
      }

      return resultado.trim() || '0m';
    },
  },
};
</script>

<style scoped>
.info-servicio {
  font-size: 11px;
  line-height: 1.35;
}

/* los datos fluyen uno tras otro y solo saltan de línea cuando no entran */
.datos {
  display: flex;
  flex-wrap: wrap;
  column-gap: 14px;
}

/* sin recortes: si un valor es largo, parte de línea pero se lee completo */
.dato {
  max-width: 100%;
}

.servicios {
  font-size: 11px;
  line-height: 1.5;
}

.servicios .titulo {
  font-weight: 700;
  letter-spacing: 0.02em;
  margin-right: 4px;
}
</style>
