package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/exec"
	"strconv"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"github.com/google/uuid"
	"github.com/xuri/excelize/v2"
)

const (
	educacenso_id   = "b6b720e1-bee4-4cbb-9700-f1651db45094"
	educacenso_name = "Importação Educacenso INEP"
)

type XLSXFile struct {
	Tenant string
	Path   string
	City   string
	Id     string
	Name   string
	Uf     string
	Region string
}

// Cidade do Alerta
type Cities struct {
	Id     string
	Region string
}

type ChildDocument struct {
	Name           string `json:"name"`
	Mother         string `json:"mother_name"`
	Tenant         string `json:"tenant_id"`
	City_id        string `json:"city_id"`
	Submitter      string `json:"alert_submitter_id"`
	Child_status   string `json:"child_status"`
	Alert_status   string `json:"alert_status"`
	Risk           string `json:"risk_level"`
	Id             string `json:"id"`
	Update         string `json:"updated_at"`
	Create         string `json:"created_at"`
	Case           string `json:"current_case_id"`
	Step_type      string `json:"current_step_type"`
	Step_id        string `json:"current_step_id"`
	Age            int    `json:"age"`
	Submitter_name string `json:"alert_submitter_name"`
	City_name      string `json:"city_name"`
	Uf             string `json:"uf"`
	Country_region string `json:"country_region"`
	Step_slug      string `json:"step_slug"`
	Step_name      string `json:"step_name"`
}

func get_step_type_name() map[string]string {
	return map[string]string{
		"alert":       "Alerta",
		"pesquisa":    "Pesquisa",
		"analise":     "AnaliseTecnica",
		"gestao":      "GestaoDoCaso",
		"rematricula": "Rematricula",
		"observacao":  "Observacao",
	}
}

func get_step_type(slash string, name_step string) string {
	return "BuscaAtivaEscolar" + slash + "CaseSteps" + slash + get_step_type_name()[name_step]
}

func get_step_index() map[string]int {
	return map[string]int{
		"alert":       10,
		"pesquisa":    20,
		"analise":     30,
		"gestao":      40,
		"rematricula": 50,
		"obs1":        60,
		"obs2":        70,
		"obs3":        80,
		"obs4":        90,
	}
}

func get_report_index() map[string]int {
	return map[string]int{
		"obs1": 1,
		"obs2": 2,
		"obs3": 3,
		"obs4": 4,
	}
}

func get_state() map[string]string {
	return map[string]string{
		"Acre":                "AC",
		"Alagoas":             "AL",
		"Amapá":               "AP",
		"Amazonas":            "AM",
		"Bahia":               "BA",
		"Ceará":               "CE",
		"Distrito Federal":    "DF",
		"Espírito Santo":      "ES",
		"Goiás":               "GO",
		"Maranhão":            "MA",
		"Mato Grosso":         "MT",
		"Mato Grosso do Sul":  "MS",
		"Minas Gerais":        "MG",
		"Pará":                "PA",
		"Paraíba":             "PB",
		"Paraná":              "PR",
		"Pernambuco":          "PE",
		"Piauí":               "PI",
		"Rio de Janeiro":      "RJ",
		"Rio Grande do Norte": "RN",
		"Rio Grande do Sul":   "RS",
		"Rondônia":            "RO",
		"Roraima":             "RR",
		"Santa Catarina":      "SC",
		"São Paulo":           "SP",
		"Sergipe":             "SE",
		"Tocantins":           "TO",
	}
}

func get_place_kind() map[string]string {
	return map[string]string{
		"URBANA": "urban",
		"RURAL":  "rural",
	}
}

func gerenate_ids() map[string]string {
	return map[string]string{
		"case":        uuid.New().String(),
		"alert":       uuid.New().String(),
		"child":       uuid.New().String(),
		"analise":     uuid.New().String(),
		"gestao":      uuid.New().String(),
		"pesquisa":    uuid.New().String(),
		"rematricula": uuid.New().String(),
		"obs1":        uuid.New().String(),
		"obs2":        uuid.New().String(),
		"obs3":        uuid.New().String(),
		"obs4":        uuid.New().String(),
	}
}

func get_today() string {
	return time.Now().Format("2006-01-02 15:04:05")
}

func get_files(db *sql.DB) ([][]string, error) {

	res, err := db.Query("select tenant_id, concat(\"/var/www/bae-api/storage/app/\", path), t.city_id, ij.id, c.name, t.uf,  c.region  from import_jobs ij join tenants t on ij.tenant_id = t.id  join cities c on t.city_id = c.id where status = 'pending' and `type` like '%educacenso%' order by ij.id asc")

	if err != nil {
		return nil, err
	}

	defer res.Close()

	var files_data [][]string

	for res.Next() {

		var xlsxfile XLSXFile

		err := res.Scan(&xlsxfile.Tenant, &xlsxfile.Path, &xlsxfile.City, &xlsxfile.Id, &xlsxfile.Name, &xlsxfile.Uf, &xlsxfile.Region)

		if err != nil {
			log.Fatal(err)
		}

		files_data = append(files_data, []string{xlsxfile.Tenant, xlsxfile.Path, xlsxfile.City, xlsxfile.Id, xlsxfile.Name, xlsxfile.Uf, xlsxfile.Region})
	}

	return files_data, nil

}

func check_children(year string, id string, city string, db *sql.DB) string {
	var child_exists string
	query := fmt.Sprintf("select id from children where educacenso_year = '%s' and educacenso_id = '%s' and city_id = '%s' and deleted_at is null", year, id, city)
	err := db.QueryRow(query).Scan(&child_exists)
	if err != nil {
		//log.Fatal(err)
	}
	return child_exists

}

func get_dob(dob_file string) string {
	var date []string
	if strings.Contains(dob_file, "-") {
		date = strings.Split(dob_file, "-")
	} else {
		date = strings.Split(dob_file, "/")
	}
	var year string
	if date[2][0:1] != "9" {
		year = "20" + date[2][0:2]
	} else {
		year = "19" + date[2][0:2]
	}
	return year + "-" + date[0] + "-" + date[1]
}

func get_age(dob string) int {
	dob_converted, err := time.Parse("2006-01-02", dob)

	if err != nil {
	}

	difference := time.Now().Sub(dob_converted)
	return int(difference.Hours() / 24 / 365)
}

func get_guardian(mother_name string) (int, string) {
	if mother_name != "" {
		return 1, "mother"
	}
	return 0, "null"
}

func get_info_city(name string, uf string, db *sql.DB) Cities {
	var city_info Cities
	query := fmt.Sprintf("select id, region from cities where name = '%s' and uf = '%s'", name, uf)
	err := db.QueryRow(query).Scan(&city_info.Id, &city_info.Region)
	if err != nil {
		//log.Fatal(err)
	}
	return city_info
}

func check_header(header []string) bool {
	header_educacenso := []string{"ESTADO", "MUNICIPIO", "DEPENDENCIA", "CATEGORIA", "CONVENIO", "LOCALIZACAO", "COD ESCOLA", "NOME ESCOLA", "COD ALUNO", "NOME ALUNO", "NASCIMENTO", "NOME MAE", "MODALIDADE", "ETAPA"}
	for i, h := range header_educacenso {
		if h != header[i] {
			return false
		}
	}
	return true
}

func insert_elastic(id string, data string) {
	var url = "http://localhost:9200/children/child/" + id
	var content = "Content-Type: application/json"
	c := exec.Command("curl", "-X", "PUT", "-H", content, "-d", data, url, "-v")
	c.Stdout = os.Stdout
	c.Stderr = os.Stderr
	err := c.Run()
	if err != nil {
		fmt.Println("Error: ", err)
	}

}

func cannot_handle_file(file_data map[string]string, db *sql.DB) {
	currentTime := get_today()
	query := "update import_jobs set status='failed', errors='[\"" + file_data["error"] + "\"]', updated_at = '" + currentTime + "' where id = '" + file_data["id"] + "'"
	_, err := db.Exec(query)

	if err != nil {
		return
	}
	return
}

func process_file(file map[string]string, db *sql.DB) string {
	f, err := excelize.OpenFile(file["path"])
	if err != nil {
		return "Cannot read file"
	}

	defer func() {
		if err := f.Close(); err != nil {
			//
		}
	}()

	rows, err := f.GetRows("Dados")

	if err != nil || len(rows[3]) < 15 || rows[3][1] != "ESTADO" {
		return "Cannot read file"
	}

	headers := []string{rows[3][1], rows[3][2], rows[3][3], rows[3][4], rows[3][5], rows[3][6], rows[3][7], rows[3][8], rows[3][9], rows[3][10], rows[3][11], rows[3][12], rows[3][13], rows[3][14]}

	if check_header(headers) == false {
		return "Cabeçalho padrão do Educacenso não localizado."
	}

	currentTime := get_today()

	states := get_state()

	place_kind := get_place_kind()

	step_index := get_step_index()

	report_index := get_report_index()

	for i := 4; i < len(rows)-3; i++ {
		if len(rows[i]) < 15 {
			continue
		}
		check_children := check_children("2022", rows[i][9], file["city_id"], db)
		if check_children != "" {
			continue
		}

		ids := gerenate_ids()
		xlsx_file_data := map[string]string{
			"uf":           rows[i][1],
			"city":         rows[i][2],
			"dependency":   rows[i][3],
			"category":     rows[i][4],
			"agreement":    rows[i][5],
			"location":     rows[i][6],
			"cod_school":   rows[i][7],
			"school":       rows[i][8],
			"cod_child":    rows[i][9],
			"child_name":   rows[i][10],
			"dob":          rows[i][11],
			"child_mother": rows[i][12],
			"modality":     rows[i][13],
			"stage":        rows[i][14],
		}
		dob := get_dob(xlsx_file_data["dob"])
		age := get_age(dob)
		has_parent, guardian := get_guardian(xlsx_file_data["child_mother"])
		var city_info Cities = get_info_city(xlsx_file_data["city"], states[xlsx_file_data["uf"]], db)
		observation := "Escola: " + xlsx_file_data["school"] + " | Modalidade de ensino: " + xlsx_file_data["modality"] + " | Etapa: " + xlsx_file_data["stage"]

		alert_query := fmt.Sprintf("insert into case_steps_alerta values ('%s', '%s', 0, '%s', '%s', '%s', '%d', '%d', '%s', '%s', null, 0, null, '%s', null, null, '%s', null, null, null, null, 500, '%s' , null, null, null, null, null, null, null, null, null, '%s', '%s', null, null, '%s', '%s', null, '%s', 'pending', null, null, null, null, null, '%s')", ids["alert"], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "alert"), step_index["alert"], step_index["pesquisa"], get_step_type("\\\\", "pesquisa"), educacenso_id, xlsx_file_data["child_name"], dob, xlsx_file_data["child_mother"], strings.ToUpper(xlsx_file_data["city"]), states[xlsx_file_data["uf"]], currentTime, currentTime, city_info.Id, observation)
		db.Exec(alert_query)

		pesquisa_query := fmt.Sprintf("insert into case_steps_pesquisa values ('%s', '%s', 0, '%s', '%s', '%s', '%d', '%d', '%s', null, null, 0, null, '%s', null, null, '%s', null, null, null, null, 0, null, null, null, '%s', null, null, null, null, null, null, null, null, '%d', null, null, '%s', null, '%s', '%s', null, null, null, null, null, null, null, null, null, null, null, null, null, null, '%s', '%s', '%s', null, '%s', '%s', null, '%s' , '%s', null, null, null, null, null, null, null, null)", ids["pesquisa"], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "pesquisa"), step_index["pesquisa"], step_index["analise"], get_step_type("\\\\", "analise"), xlsx_file_data["child_name"], dob, xlsx_file_data["school"], has_parent, guardian, xlsx_file_data["child_mother"], xlsx_file_data["child_mother"], strings.ToUpper(xlsx_file_data["city"]), states[xlsx_file_data["uf"]], place_kind[xlsx_file_data["location"]], currentTime, currentTime, city_info.Id, xlsx_file_data["cod_school"])
		db.Exec(pesquisa_query)

		analise_query := fmt.Sprintf("insert into case_steps_analise_tecnica values('%s', '%s', 0, '%s', '%s', '%s', '%d', '%d', '%s', null, null, 0, null, null, '%s', '%s', null, null)", ids["analise"], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "analise"), step_index["analise"], step_index["gestao"], get_step_type("\\\\", "gestao"), currentTime, currentTime)
		db.Exec(analise_query)

		gestao_query := fmt.Sprintf("insert into case_steps_gestao_do_caso values ('%s', '%s', 0, '%s', '%s', '%s', '%d', '%d', '%s', null, null, 0, null, null, null, null, '%s', '%s', null, null)", ids["gestao"], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "gestao"), step_index["gestao"], step_index["rematricula"], get_step_type("\\\\", "rematricula"), currentTime, currentTime)
		db.Exec(gestao_query)

		rematricula_query := fmt.Sprintf("insert into case_steps_rematricula values ('%s', '%s', 0, '%s', '%s', '%s', '%d', '%d', '%s', null, null, 0, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, '%s', '%s', null, null, null, null)", ids["rematricula"], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "rematricula"), step_index["rematricula"], step_index["obs1"], get_step_type("\\\\", "observacao"), currentTime, currentTime)
		db.Exec(rematricula_query)

		for j := 1; j < 4; j++ {
			obs_index := "obs" + strconv.Itoa(j)
			obs_next_index := "obs" + strconv.Itoa(j+1)
			obs_query := fmt.Sprintf("insert into case_steps_observacao values ('%s', '%s', 0, '%s', '%s', '%s', '%d', '%d', '%s', null, null, 0, null, null, '%d', null, null, null, '%s', '%s', null, null, null, null, null)", ids[obs_index], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "observacao"), step_index[obs_index], step_index[obs_next_index], get_step_type("\\\\", "observacao"), report_index[obs_index], currentTime, currentTime)
			db.Exec(obs_query)
		}

		obs_query_fourth := fmt.Sprintf("insert into case_steps_observacao values ('%s', '%s', 0, '%s', '%s', '%s', '%d', null, null, null, null, 0, null, null, '%d', null, null, null, '%s', '%s', null, null, null, null, null)", ids["obs4"], file["tenant_id"], ids["child"], ids["case"], get_step_type("\\\\", "observacao"), step_index["obs4"], report_index["obs4"], currentTime, currentTime)
		db.Exec(obs_query_fourth)

		children_query := fmt.Sprintf("insert into children values ('%s', '%s', '%s', 'out_of_school', '%s', '%s', null, '%d', null, 'medium', '%s', '%s', '%s', '%s', '%s', null, 'pending', 'normal', '%s', null, null, null, null, '%s', '2022', null)", ids["child"], file["tenant_id"], file["city_id"], xlsx_file_data["child_name"], xlsx_file_data["child_mother"], age, ids["case"], get_step_type("\\\\", "alert"), ids["alert"], currentTime, currentTime, educacenso_id, xlsx_file_data["cod_child"])
		db.Exec(children_query)

		linked_steps := fmt.Sprintf("[{ \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":\"%s\", \"index\":%d }, \"type\":\"%s\", \"index\":%d }, { \"id\":\"%s\", \"next\":{ \"type\":null, \"index\":null }, \"type\":\"%s\", \"index\":%d }]", ids["alert"], get_step_type("\\\\\\\\", "pesquisa"), step_index["pesquisa"], get_step_type("\\\\\\\\", "alert"), step_index["alert"], ids["pesquisa"], get_step_type("\\\\\\\\", "analise"), step_index["analise"], get_step_type("\\\\\\\\", "pesquisa"), step_index["pesquisa"], ids["analise"], get_step_type("\\\\\\\\", "gestao"), step_index["gestao"], get_step_type("\\\\\\\\", "analise"), step_index["analise"], ids["gestao"], get_step_type("\\\\\\\\", "rematricula"), step_index["rematricula"], get_step_type("\\\\\\\\", "gestao"), step_index["gestao"], ids["rematricula"], get_step_type("\\\\\\\\", "observacao"), step_index["obs1"], get_step_type("\\\\\\\\", "rematricula"), step_index["rematricula"], ids["obs1"], get_step_type("\\\\\\\\", "observacao"), step_index["obs2"], get_step_type("\\\\\\\\", "observacao"), step_index["obs1"], ids["obs2"], get_step_type("\\\\\\\\", "observacao"), step_index["obs3"], get_step_type("\\\\\\\\", "observacao"), step_index["obs2"], ids["obs3"], get_step_type("\\\\\\\\", "observacao"), step_index["obs4"], get_step_type("\\\\\\\\", "observacao"), step_index["obs3"], ids["obs4"], get_step_type("\\\\\\\\", "observacao"), step_index["obs4"])
		children_cases_query := fmt.Sprintf("insert into children_cases values('%s', '%s', '%s', 'in_progress', '2022/1', 'medium', 1, null, null, '%s', null, 500, '%s', '%s', '%s', '%s', '%s', null, null, null, null, null)", ids["case"], file["tenant_id"], ids["child"], educacenso_id, ids["alert"], get_step_type("\\\\", "alert"), linked_steps, currentTime, currentTime)
		db.Exec(children_cases_query)

		children := ChildDocument{
			Name:           xlsx_file_data["child_name"],
			Mother:         xlsx_file_data["child_mother"],
			Tenant:         file["tenant_id"],
			City_id:        file["city_id"],
			Submitter:      educacenso_id,
			Child_status:   "out_of_school",
			Alert_status:   "pending",
			Risk:           "medium",
			Id:             ids["child"],
			Update:         currentTime,
			Create:         currentTime,
			Case:           ids["case"],
			Step_type:      get_step_type("\\", "alert"),
			Step_id:        ids["alert"],
			Age:            age,
			Submitter_name: educacenso_id,
			City_name:      file["tenant_name"],
			Uf:             file["tenant_uf"],
			Country_region: file["tenant_region"],
			//Step_slug:      "alerta",
			//Step_name:      "Alerta",
		}

		dataJSON, err := json.Marshal(children)
		js := string(dataJSON)
		if err != nil {
			panic(err.Error())
			//return
		}

		insert_elastic(ids["child"], js)
	}
	_, err_update := db.Exec("UPDATE import_jobs SET status='completed', updated_at = ?  WHERE id=?", currentTime, file["id"])

	if err_update != nil {
		panic(err_update.Error())
		//return
	}
	return "No"
}

func main() {
	db, err := sql.Open("mysql", "root:root@tcp(172.17.0.3:3306)/busca_ativa_escolar")

	if err != nil {
		log.Fatal(err)
	}

	all_files, err := get_files(db)
	for i := 0; i < len(all_files); i++ {
		file := map[string]string{
			"path":          all_files[i][1],
			"tenant_id":     all_files[i][0],
			"city_id":       all_files[i][2],
			"id":            all_files[i][3],
			"tenant_name":   all_files[i][4],
			"tenant_uf":     all_files[i][5],
			"tenant_region": all_files[i][6],
		}
		file["error"] = process_file(file, db)
		if file["error"] != "No" {
			cannot_handle_file(file, db)
		}

	}
}
