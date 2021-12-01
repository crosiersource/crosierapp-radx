<template>
  <div :class="'col-md-' + this.col">
    <div class="form-group">
      <label v-if="this.showLabel" :for="this.id">{{ this.label }}</label>
      <Calendar
        :id="this.id"
        :inputClass="this.inputClass"
        :class="'form-control ' + (this.error ? 'is-invalid' : '')"
        :modelValue="modelValue"
        :selectionMode="this.selectionMode"
        ref="refCalendar"
        @input="this.onInput"
        @date-select="this.onInput"
        dateFormat="dd/mm/yy"
        :showTime="this.showTime"
        :showSeconds="this.showSeconds"
        :showButtonBar="true"
        :showIcon="true"
        :showOnFocus="false"
        :disabled="this.disabled"
        :autoZIndex="this.autoZIndex"
        :baseZIndex="this.baseZIndex"
        @focus="this.$emit('focus')"
        @blur="this.$emit('blur')"
      />
      <small v-if="this.helpText" :id="this.id + '_help'" class="form-text text-muted">{{
        this.helpText
      }}</small>
      <div class="invalid-feedbackk blink" v-show="this.error">
        {{ this.error }}
      </div>
    </div>
  </div>
</template>

<script>
import Calendar from "primevue/calendar";

export default {
  name: "CrosierCalendar",

  components: {
    Calendar,
  },

  emits: ["update:modelValue", "date-select"],

  props: {
    modelValue: {},
    id: {
      type: String,
      required: true,
    },
    error: {
      type: String,
      default: null,
    },
    col: {
      type: String,
      default: "12",
    },
    label: {
      type: String,
      required: true,
    },
    showTime: {
      type: Boolean,
      default: false,
    },
    showSeconds: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    helpText: {
      type: String,
    },
    selectionMode: {
      type: String,
      default: "single",
    },
    autoZIndex: {
      type: Boolean,
      default: true,
    },
    baseZIndex: {
      type: Number,
      default: 0,
    },
    showLabel: {
      type: Boolean,
      default: true,
    },
  },

  data() {
    return {
      inputClass: null,
    };
  },

  mounted() {
    if (this.selectionMode === "range") {
      this.inputClass = "crsr-date-periodo text-center";
    } else if (this.showSeconds) {
      this.inputClass = "crsr-datetime";
    } else if (this.showTime) {
      this.inputClass = "crsr-datetime-nseg";
    } else {
      this.inputClass = "crsr-date";
    }
  },

  updated() {
    document.querySelectorAll(".crsr-date").forEach(function format(el) {
      // eslint-disable-next-line no-new,no-undef
      new Cleave(el, {
        date: true,
        delimiter: "/",
        datePattern: ["d", "m", "Y"],
      });
    });

    document.querySelectorAll(".crsr-datetime").forEach(function format(el) {
      el.maxLength = 19; // 01/02/1903 12:34:56
      // eslint-disable-next-line no-new,no-undef
      new Cleave(el, {
        numeralPositiveOnly: true,
        delimiters: ["/", "/", " ", ":"],
        blocks: [2, 2, 4, 2, 2, 2],
      });
    });

    document.querySelectorAll(".crsr-datetime-nseg").forEach(function format(el) {
      el.maxLength = 17; // 01/02/1903 12:34
      // eslint-disable-next-line no-new, no-undef
      new Cleave(el, {
        numeralPositiveOnly: true,
        delimiters: ["/", "/", " ", ":"],
        blocks: [2, 2, 4, 2, 2],
      });
    });

    document.querySelectorAll(".crsr-date-periodo").forEach(function format(el) {
      el.maxLength = 23; // 01/02/1903 12:34:56
      // eslint-disable-next-line no-new,no-undef
      new Cleave(el, {
        numeralPositiveOnly: true,
        delimiters: ["/", "/", " - ", "/", "/"],
        blocks: [2, 2, 4, 2, 2, 4],
      });
    });
  },

  methods: {
    onInput($event) {
      this.$nextTick(() => {
        const dtStr = $event?.target?.value ?? $event;

        let dateParser = null;
        let date = null;
        let match = null;
        let dtIni = null;
        let dtFim = null;

        if (dtStr instanceof Date) {
          date = dtStr;
        } else if (this.inputClass === "crsr-date") {
          if (dtStr.length === 10) {
            dateParser = /(\d{2})\/(\d{2})\/(\d{4})/;
            match = dtStr.match(dateParser);
            date = new Date(
              match[3], // year
              match[2] - 1, // monthIndex
              match[1] // day
              // match[4],  // hours
              // match[5],  // minutes
              // match[6]  //seconds
            );
          }
        } else if (dtStr.length === 16 && this.inputClass === "crsr-datetime-nseg") {
          dateParser = /(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})/;
          match = dtStr.match(dateParser);
          date = new Date(
            match[3], // year
            match[2] - 1, // monthIndex
            match[1], // day
            match[4], // hours
            match[5] // minutes
            // match[6]  //seconds
          );
        } else if (dtStr.length === 19 && this.inputClass === "crsr-datetime") {
          dateParser = /(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})/;
          match = dtStr.match(dateParser);
          date = new Date(
            match[3], // year
            match[2] - 1, // monthIndex
            match[1], // day
            match[4], // hours
            match[5], // minutes
            match[6] // seconds
          );
        } else if (dtStr.length === 23 && this.selectionMode === "range") {
          dateParser = /(\d{2})\/(\d{2})\/(\d{4}) - (\d{2})\/(\d{2})\/(\d{4})/;
          match = dtStr.match(dateParser);
          dtIni = new Date(
            match[3], // year
            match[2] - 1, // monthIndex
            match[1] // day
          );
          dtFim = new Date(
            match[6], // year
            match[5] - 1, // monthIndex
            match[4] // day
          );
          if (dtIni && dtFim) {
            const dts = [dtIni, dtFim];
            this.$emit("update:modelValue", dts);
          }
        }

        if (date) {
          this.$emit("update:modelValue", date);
          this.$emit("date-select", $event);
        } else {
          this.$emit("update:modelValue", dtStr);
        }
      });
    },
  },
};
</script>
<style scoped>
.invalid-feedbackk {
  width: 100%;
  margin-top: 0.25rem;
  font-size: 80%;
  color: #e55353;
}
</style>
